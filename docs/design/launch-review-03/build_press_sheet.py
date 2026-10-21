#!/usr/bin/env python3
from pathlib import Path
import json, hashlib, zipfile
from reportlab.pdfgen import canvas
from reportlab.lib.colors import HexColor
from reportlab.lib.styles import ParagraphStyle
from reportlab.platypus import Paragraph
from reportlab.lib.enums import TA_LEFT
from xml.sax.saxutils import escape

import argparse
parser=argparse.ArgumentParser(description='Build the confirmed-lineup press sheet; no website access.')
parser.add_argument('--output',type=Path,default=Path('press-sheet-output'))
args=parser.parse_args()
OUT = args.output
OUT.mkdir(parents=True, exist_ok=True)
ROSTER = [
    {'name': 'Chad Lindsey', 'slug': 'chad-lindsey', 'role': 'Guitar, keyboards and vocals', 'bio': 'Chad Lindsey plays guitar and keyboards and provides vocals for Spiritual Gorillaz.'},
    {'name': 'Ron Green', 'slug': 'ron-green', 'role': 'Harmonica', 'bio': 'Ron Green plays harmonica for Spiritual Gorillaz.'},
    {'name': 'Charlie Savell', 'slug': 'charlie-savell', 'role': 'Drums', 'bio': 'Charlie Savell plays drums for Spiritual Gorillaz.'},
]
INTRO = 'Spiritual Gorillaz brings together Chad Lindsey on guitar, keyboards and vocals, Ron Green on harmonica, and Charlie Savell on drums.'
BOOKING = 'For an event inquiry, share your preferred date, venue and location, event type, expected audience, and sound or production arrangements.'
WEBSITE = 'https://www.spiritualgorillaz.com/'
BOOKING_URL = WEBSITE + 'booking/'
PDF = OUT / 'Spiritual-Gorillaz-Band-and-Booking-Sheet.pdf'
navy=HexColor('#071321'); gold=HexColor('#D8B567'); ink=HexColor('#17283B'); muted=HexColor('#516074'); cream=HexColor('#FAF8F2'); line=HexColor('#D8D6CE')
c=canvas.Canvas(str(PDF), pagesize=(612,792), pageCompression=1, invariant=1)
c.setTitle('Spiritual Gorillaz | Band and Booking Sheet')
c.setAuthor('Spiritual Gorillaz')
c.setSubject('Confirmed band lineup and website booking route; prepared for publication review.')
c.setCreator('Spiritual Gorillaz content preparation')
c.setKeywords('Spiritual Gorillaz, lineup, band, booking, Chad Lindsey, Ron Green, Charlie Savell')
c.setFillColor(cream); c.rect(0,0,612,792,fill=1,stroke=0)
c.setFillColor(navy); c.rect(0,574,612,218,fill=1,stroke=0)
c.setStrokeColor(HexColor('#163047')); c.setLineWidth(.6)
for i in range(8):
    c.line(395+i*24,574,575+i*24,792)
c.setStrokeColor(gold); c.setLineWidth(3); c.line(48,728,111,728)
c.setFillColor(gold); c.setFont('Helvetica-Bold',8.5); c.drawString(48,751,'BAND & BOOKING  /  CONTENT EDITION 01')
c.setFillColor(cream); c.setFont('Helvetica-Bold',47); c.drawString(45,675,'SPIRITUAL'); c.drawString(45,624,'GORILLAZ')
c.setFillColor(gold); c.setFont('Helvetica',11); c.drawString(48,596,'M U S I C .   P E O P L E .   P U R P O S E .')

placed=[]
def para(text,x,top,width,font_size=11,leading=16,color=ink,font='Helvetica',max_height=100):
    style=ParagraphStyle('p',fontName=font,fontSize=font_size,leading=leading,textColor=color,alignment=TA_LEFT)
    p=Paragraph(escape(text),style)
    w,h=p.wrap(width,max_height)
    if h>max_height:raise ValueError(f'Paragraph exceeds allotted height: {text}')
    p.drawOn(c,x,top-h);placed.append({'text':text,'box':[x,top-h,x+width,top]})
    return h

c.setFillColor(muted);c.setFont('Helvetica-Bold',8.5);c.drawString(48,544,'THE BAND')
para(INTRO,48,524,516,11.6,17,max_height=56)
for i, member in enumerate(ROSTER):
    top=444-i*73
    c.setFillColor(gold);c.setFont('Helvetica-Bold',9);c.drawString(48,top-13,f'{i+1:02d}')
    c.setFillColor(ink);c.setFont('Helvetica-Bold',19);c.drawString(78,top-13,member['name'])
    c.setFillColor(muted);c.setFont('Helvetica',11);c.drawString(78,top-35,member['role'])
    c.setStrokeColor(line);c.setLineWidth(.65);c.line(48,top-52,564,top-52)

c.setFillColor(navy);c.roundRect(48,80,516,134,8,fill=1,stroke=0)
c.setFillColor(gold);c.setFont('Helvetica-Bold',10);c.drawString(66,191,'BOOKING INQUIRIES')
para(BOOKING,66,174,474,10.5,15,cream,max_height=46)
c.setFillColor(gold);c.setFont('Helvetica-Bold',11);c.drawString(66,119,'spiritualgorillaz.com/booking/')
c.linkURL(BOOKING_URL,(65,116,265,131),relative=0,thickness=0)
c.setFillColor(HexColor('#CAD4DF'));c.setFont('Helvetica',8.5);c.drawString(66,96,'An inquiry is not a confirmed booking.')
c.setFillColor(muted);c.setFont('Helvetica',8.5);c.drawString(48,45,'spiritualgorillaz.com')
c.linkURL(WEBSITE,(47,42,144,55),relative=0,thickness=0)
c.drawRightString(564,45,'BAND & BOOKING SHEET  /  01')
c.showPage();c.save()

