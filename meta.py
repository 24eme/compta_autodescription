from pdfminer.pdfparser import PDFParser
from pdfminer.pdfdocument import PDFDocument
import glob, sys, os, hashlib
import sqlite3

def homogeneise_meta(meta):
    for m in meta:
        if isinstance(meta[m], bytes):
            if meta[m][:2] == b'\xfe\xff':
                meta[m] = meta[m][2:].decode('utf-16be')
            elif meta[m][:2] == b'\xff\xfe':
                meta[m] = meta[m][2:].decode('utf-16le')
            else:
                try:
                    meta[m] = meta[m].decode('utf-8')
                except UnicodeDecodeError:
                    try:
                        meta[m] = meta[m].decode('iso8859-1')
                    except UnicodeDecodeError:
                        try:
                            meta[m] = meta[m].decode('latin1')
                        except UnicodeDecodeError:
                            meta[m] = meta[m].decode('ascii')
        if str(meta[m]).find('Ã') == -1:
            continue
        try:
            meta[m] = meta[m].encode('iso8859-1').decode('utf-8')
        except:
            raise
    return meta

def index_pdf(file, last, conn):
    mtime = os.path.getmtime(file)
    if  mtime < last:
        return False

    fp = open(file, 'rb')
    parser = PDFParser(fp)
    doc = PDFDocument(parser)
    try:
        meta = doc.info[0]
    except IndexError:
        meta = {}
    meta = homogeneise_meta(meta)

    fp.seek(0)
    hash_md5 = hashlib.md5()
    for chunk in iter(lambda: fp.read(4096), b""):
        hash_md5.update(chunk)
    meta['md5'] = hash_md5.hexdigest()
    meta['ctime'] = os.path.getctime(file)
    meta['mtime'] = mtime

    res = conn.execute("SELECT id FROM file WHERE fullpath = \"%s\" OR md5 = \"%s\"" % (file, meta['md5']))
    has_file = res.fetchone()
    if not has_file:
        sql = "INSERT INTO file (fullpath, filename, md5, ctime, mtime) VALUES (\"%s\", \"%s\", \"%s\", %d, %d) ; " % (file, os.path.basename(file), meta['md5'], meta['ctime'], meta['mtime'])
        conn.execute(sql)
    else:
        sql = 'UPDATE file SET filename = "%s", md5 = "%s", mtime = %d WHERE fullpath = "%s" OR md5 = "%s"' % (os.path.basename(file), meta['md5'], meta['mtime'], file, meta['md5'])
        conn.execute(sql)

    res = conn.execute("SELECT * FROM piece WHERE fullpath = \"%s\" OR md5 = \"%s\"" % (file, meta['md5']))
    has_piece = res.fetchone()
    if not has_piece:
        sql = "INSERT INTO piece (fullpath, filename, md5, ctime, mtime) VALUES (\"%s\", \"%s\", \"%s\", %d, %d) ; " % (file, os.path.basename(file), meta['md5'], meta['ctime'], meta['mtime'])
        conn.execute(sql)

    sql = "UPDATE piece SET "
    sql = sql + " filename = \"%s\", extention = \"pdf\" " % file
    sql = sql + ', md5 = "%s"' % meta['md5']
    sql = sql + ', mtime = %d' % meta['mtime']

    if meta.get('facture:type'):
        sql = sql + ", facture_type = \"%s\"" % meta['facture:type']
    if meta.get('facture:author'):
        sql = sql + ", facture_author = \"%s\" " % meta['facture:author']
    if meta.get('facture:client'):
        sql = sql + ", facture_client = \"%s\" " % meta['facture:client']
    if meta.get('facture:identifier'):
        sql = sql + ", facture_identifier = \"%s\" " % meta['facture:identifier']
    if meta.get('facture:date'):
        sql = sql + ", facture_date = \"%s\" " % meta['facture:date']
    if meta.get('facture:libelle'):
        sql = sql + ", facture_libelle = \"%s\" " % meta['facture:libelle'];
    if meta.get('facture:HT'):
        sql = sql + ', facture_prix_ht = %s ' % str(meta['facture:HT']).replace(',', '.');
    if meta.get('facture:TVA'):
        sql = sql + ', facture_prix_tax = %s ' % str(meta['facture:TVA']).replace(',', '.');
    if meta.get('facture:TTC'):
        sql = sql + ', facture_prix_ttc = %s ' % str(meta['facture:TTC']).replace(',', '.');
    if meta.get('facture:devise'):
        sql = sql + ", facture_devise = \"%s\" " % meta['facture:devise']
    if meta.get('paiement:comment'):
        sql = sql + ", paiement_comment = \"%s\" " % meta['paiement:comment']
    if meta.get('paiement:proof'):
        sql = sql + ", paiement_proof = \"%s\" " % meta['paiement:proof']
    if meta.get('paiement:date'):
        sql = sql + ", paiement_date = \"%s\" " % meta['paiement:date']
    sql = sql + " WHERE fullpath = \"%s\" OR md5 = \"%s\"" % (file, meta['md5'])
    sql = sql + " ; "

    conn.execute(sql)

    return True

def index_banque(csv_url, conn):
    import csv
    import requests
    from io import StringIO
    import datetime

    imported_at = datetime.datetime.now().timestamp()
    updated_at = imported_at

    last = None
    res = conn.execute("select mtime from banque ORDER BY mtime DESC LIMIT 1;");
    fetch = res.fetchone()
    if fetch:
        last = fetch[0]

    if last and (last - updated_at < 15 * 60):
        return False

    with requests.get(csv_url, stream=True) as r:
        csv_raw = StringIO(r.text)
        csv_reader = csv.reader(csv_raw, delimiter=",")
        for csv_row in csv_reader:
            if (csv_row[0] == 'date') or (len(csv_row) < 7):
                continue
            res = conn.execute("SELECT id FROM banque WHERE date = \"%s\" AND raw = \"%s\";" % (csv_row[0], csv_row[1]))
            row = res.fetchone()
            if not row or not row[0]:
                conn.execute("INSERT INTO banque (date, raw, ctime) VALUES (\"%s\", \"%s\" , \"%s\")" % (csv_row[0], csv_row[1], imported_at) )
            sql = "UPDATE banque SET "
            sql = sql + 'amount = %s, ' % csv_row[2]
            sql = sql + 'type = "%s", ' % csv_row[3]
            sql = sql + 'banque_account = "%s", ' % csv_row[4]
            sql = sql + 'rdate = "%s", ' % csv_row[5]
            sql = sql + 'vdate = "%s", ' % csv_row[6]
            sql = sql + 'label = "%s", ' % csv_row[7]
            sql = sql + 'mtime = %d' % updated_at
            sql = sql + " WHERE date = \"%s\" AND raw = \"%s\";" % (csv_row[0], csv_row[1])
            conn.execute(sql)

    return True

def consolidate(conn):
    print("consolidate")

    res = conn.execute("SELECT id, date, raw, label FROM banque WHERE piece is null");
    proof2banqueid = {}

#    $md52pieceid = array();
    for row in res:
        if row['raw']:
            proof2banqueid[row['raw'] + 'ø' + row['date']] = row['id'];
        if row['label']:
            proof2banqueid[row['label'] + 'ø' +  row['date']] = row['id'];

    res = conn.execute("SELECT id, paiement_proof, paiement_date, fullpath, md5 FROM piece ")
    for row in res:
        banqueid = None
        if row['paiement_proof'] and row['paiement_date']:
            banqueid = proof2banqueid.get(row['paiement_proof'] + 'ø' + row['paiement_date'])
        if banqueid:
            conn.execute("UPDATE piece SET banque = %d WHERE id = %d" % (banqueid,  row['id']) )
            conn.execute("UPDATE banque SET piece = %d WHERE id = %d" % (row['id'], banqueid) )


with sqlite3.connect('db/database.sqlite') as conn:
    conn.row_factory = sqlite3.Row
    need_consolidate = False
    last = 0
    try:
        res = conn.execute("select mtime - 1 from piece ORDER BY mtime DESC LIMIT 1;");
        row = res.fetchone()
        if row:
            last = row[0]
    except sqlite3.OperationalError:
        conn.execute("CREATE TABLE piece ( id INTEGER PRIMARY KEY, filename TEXT, fullpath TEXT UNIQUE, extention TEXT, size INTEGER, ctime INTEGER, mtime INTEGER, md5 TEXT, facture_type TEXT, facture_author TEXT, facture_client TEXT, facture_identifier TEXT, facture_date DATE, facture_libelle TEXT, facture_prix_ht FLOAT, facture_prix_tax FLOAT, facture_prix_ttc FLOAT, facture_devise TEXT, paiement_comment TEXT, paiement_date DATE, paiement_proof TEXT, banque INTEGER, exercice_comptable TEXT, CONSTRAINT constraint_name UNIQUE (md5) );");
        conn.execute("CREATE TABLE file (id INTEGER PRIMARY KEY, filename TEXT, fullpath TEXT UNIQUE, extention TEXT, size INTEGER, ctime INTEGER, mtime INTEGER, md5 TEXT, piece INTEGER);");
        conn.execute("CREATE TABLE banque (id INTEGER PRIMARY KEY, date DATE, raw TEXT, amount FLOAT, type TEXT, banque_account TEXT, rdate DATE, vdate DATE, label TEXT, piece INTEGER, ctime INTEGER, mtime INTEGER, CONSTRAINT constraint_name UNIQUE (date, raw) );");

    for file in glob.glob(sys.argv[1]+'/**/*pdf', recursive=True):
        need_consolidate = index_pdf(file, last, conn) or need_consolidate

    need_consolidate = index_banque('https://raw.githubusercontent.com/24eme/banque/master/data/history.csv', conn) or need_consolidate

    if not need_consolidate:
        consolidate(conn)
        conn.commit()
