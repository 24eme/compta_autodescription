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
        return

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

    meta['mtime'] = mtime


    res = conn.execute("SELECT id FROM file WHERE fullpath = \"%s\" OR md5 = \"%s\"" % (file, meta['md5']))
    has_file = res.fetchone()
    if not has_file:
        sql = "INSERT INTO file (fullpath, filename, md5, mtime) VALUES (\"%s\", \"%s\", \"%s\", %d ) ; " % (file, os.path.basename(file), meta['md5'], meta['mtime'])
        conn.execute(sql)
    else:
        sql = 'UPDATE file SET filename = "%s", md5 = "%s", mtime = %d) WHERE fullpath = "%s" OR md5 = "%s"' % (os.path.basename(file), meta['md5'], meta['mtime'], file, meta['md5'])

    res = conn.execute("SELECT * FROM piece WHERE fullpath = \"%s\" OR md5 = \"%s\"" % (file, meta['md5']))
    has_piece = res.fetchone()
    if not has_piece:
        sql = "INSERT INTO piece (fullpath, filename, md5, mtime) VALUES (\"%s\", \"%s\", \"%s\", %d ) ; " % (file, os.path.basename(file), meta['md5'], meta['mtime'])
        conn.execute(sql)

    sql = "UPDATE piece SET "
    sql = sql + " filename = \"%s\", extention = \"pdf\" " % file
    sql = sql + ", md5 = '%s'" % meta['md5']
    sql = sql + ", mtime = '%d'" % meta['mtime']

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

with sqlite3.connect('db/database.sqlite') as conn:
    try:
        res = conn.execute("select mtime - 1 from piece ORDER BY mtime DESC LIMIT 1;");
        last = res.fetchone()[0]
    except sqlite3.OperationalError:
        conn.execute("CREATE TABLE piece ( id INTEGER PRIMARY KEY, filename TEXT, fullpath TEXT UNIQUE, extention TEXT, size INTEGER, mtime INTEGER, md5 TEXT, facture_type TEXT, facture_author TEXT, facture_client TEXT, facture_identifier TEXT, facture_date DATE, facture_libelle TEXT, facture_prix_ht FLOAT, facture_prix_tax FLOAT, facture_prix_ttc FLOAT, facture_devise TEXT, paiement_comment TEXT, paiement_date DATE, paiement_proof TEXT, banque INTEGER, exercice_comptable TEXT, CONSTRAINT constraint_name UNIQUE (md5) );");
        conn.execute("CREATE TABLE file (id INTEGER PRIMARY KEY, filename TEXT, fullpath TEXT UNIQUE, extention TEXT, size INTEGER, ctime INTEGER, mtime INTEGER, md5 TEXT, piece INTEGER);");
        conn.execute("CREATE TABLE banque (id INTEGER PRIMARY KEY, date DATE, mtime INTEGER, raw TEXT, amount FLOAT, type TEXT, banque_account TEXT, rdate DATE, vdate DATE, label TEXT, piece INTEGER, imported_at INTEGER, CONSTRAINT constraint_name UNIQUE (date, raw) );");
        last = 0

    for file in glob.glob(sys.argv[1]+'/**/*pdf', recursive=True):
        index_pdf(file, last, conn)
