from django.shortcuts import render
from django.http import HttpResponse
from django.template import loader

from pdf.models import Banque
from pdf.models import Piece
from pdf.models import File
import Indexer

import os

def index(request):
    return HttpResponse("Hello, world. You're at the polls index.")

def banque_list(request):
    Indexer.Indexer.update()
    context = {
        "banques": Banque.objects.order_by('ctime')
    }
    return render(request, "banque_list.html", context)

def piece_list(request):
    Indexer.Indexer.update()
    context = {
        "pieces": Piece.objects.order_by('-facture_date')
    }
    return render(request, "piece_list.html", context)


def file_list(request):
    Indexer.Indexer.update()
    context = {
        "files": File.objects.order_by('-ctime')
    }
    return render(request, "file_list.html", context)

def pdf_edit(request, md5):
    files = File.objects.filter(md5=md5).order_by('-mtime')
    banque = None
    if request.GET.get('banque_id'):
        banque = Banque.objects.get(pk=request.GET.get('banque_id'))
    context = {
        "file": files[0],
        "pdf_edit_full_url": os.environ.get('COMPTA_PDF_URL')+files[0].fullpath.replace(os.environ.get('COMPTA_PDF_BASE_PATH'), ''),
        "banque": banque,
        "back_banque": request.GET.get('back') == 'banque'
    }
    return render(request, "pdf_edit.html", context)

def compare_strings(a, b):
    if not a:
        return 1
    a = a.upper()
    b = b.upper()
    a_ngrams = list()
    b_ngrams = list()
    for i in range(0, len(a) - 4):
        a_ngrams.append(a[i:i+4])

    if len(a) <= 4:
        a_ngrams.append(a)

    for i in range(0, len(b) - 4):
        b_ngrams.append(b[i:i+4])

    return 1 - len(list(set(a_ngrams) & set(b_ngrams))) / len(a_ngrams);


def piece_associate_banque(request, md5):
    piece = Piece.objects.filter(md5=md5).first()
    file = piece.getFile()
    banques = {}
    for banque in Banque.objects.filter(piece=None):
        distance = 0
        nb = 0
        if piece.facture_author != "24eme" and piece.facture_author != "24ème":
            distance += compare_strings(piece.facture_author, banque.raw)
            nb += 1
        distance += compare_strings(piece.facture_client, banque.raw)
        nb += 1
        distance += compare_strings(piece.facture_libelle, banque.raw)
        nb += 1
        distance += compare_strings(piece.fullpath, banque.raw)
        nb += 1
        distance += compare_strings(piece.filename, banque.raw)
        nb += 1
        thediff = (int(banque.date.strftime('%s')) - int(piece.facture_date.strftime('%s'))) / (60*60*24*30)
        if piece.facture_date and thediff <= 1:
            distance += thediff
            nb += 1
        if piece.facture_prix_ttc and banque.amount:
            if piece.facture_prix_ttc == banque.amount:
                distance += 0
                nb += 4
            else:
                distance += (abs(piece.facture_prix_ttc - abs(banque.amount)) / piece.facture_prix_ttc ) * 2
                nb += 2
        if thediff >=0:
            banques[banque.id] = {"distance": distance/nb, "banque": banque}

    banques = dict(sorted(banques.items(), key=lambda x: x[1]['distance']))
    return render(request, "piece_associate_banque.html", {"banques": banques, "piece": piece, "file": file, 'file_date': file.getDate()})

def banque_associate_file(request, banque_id):
    banque = Banque.objects.get(pk=banque_id)
    pieces = {}
    for piece in Piece.objects.filter(banque=None):
        distance = 0
        nb = 0
        distance += compare_strings(piece.facture_author, banque.raw)
        nb += 1
        distance += compare_strings(piece.facture_client, banque.raw)
        nb += 1
        distance += compare_strings(piece.facture_libelle, banque.raw)
        nb += 1
        distance += compare_strings(piece.fullpath, banque.raw)
        nb += 1
        distance += compare_strings(piece.filename, banque.raw)
        nb += 1
        distance += compare_strings(piece.facture_identifier, banque.raw)
        nb += 1
        if piece.facture_date:
            thediff = (int(banque.date.strftime('%s')) - int(piece.facture_date.strftime('%s'))) / (60*60*24*30)
            if thediff <= 1:
                distance += thediff
                nb += 1
        distance += 2 * abs(abs(piece.facture_prix_ttc) - abs(banque.amount)) / abs(piece.facture_prix_ttc)
        nb += 2
        file = piece.getFile()
        if file:
            distance += compare_strings(file.fullpath, banque.raw)
            nb += 1
            distance += compare_strings(file.filename, banque.raw)
            nb += 1
            thediff = (file.ctime - int(banque.date.strftime('%s'))) / (60*60*24*30)
            if thediff <= 1:
                distance += thediff
                nb += 1
        pieces[piece.md5] = {"distance": distance/nb, "piece": piece}

    for file in File.objects.filter(piece_id=None):
        distance += compare_strings(file.fullpath, banque.raw)
        nb += 1
        distance += compare_strings(file.filename, banque.raw)
        nb += 1
        #Pas de montant
        distance += 1
        nb += 1
        thediff = (int(banque.date.strftime('%s')) - file.ctime) / (60*60*24*30)
        if thediff <= 1:
            distance += thediff
            nb += 1
        pieces[file.md5] = {"distance": distance/nb, "file": file}

    pieces = dict(sorted(pieces.items(), key=lambda x: x[1]['distance']))
    return render(request, "banque_associate_file.html", {"pieces": pieces, "banque": banque})
