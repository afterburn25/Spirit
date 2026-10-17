#!/usr/bin/env python3
"""Final crop, stacking and responsive polish for dedicated page masters."""
from pathlib import Path

css_path = Path("assets/css/section-pages.css")
css = css_path.read_text(encoding="utf-8")
marker = "/* === DEDICATED MASTER POLISH === */"
if marker in css:
    raise RuntimeError("Dedicated master polish already exists")
css += r'''

/* === DEDICATED MASTER POLISH === */
/* Keep all real page content above the decorative Booking artwork. */
.concept-booking>div:first-child{position:relative;isolation:isolate;overflow:hidden;padding-bottom:230px}
.concept-booking>div:first-child>*:not(.booking-native-art){position:relative;z-index:2}
.concept-booking .booking-native-art{
  z-index:0;left:-4%;bottom:0;width:108%;height:285px;
  object-fit:cover;object-position:43% 68%;opacity:.64;
  filter:brightness(.70) contrast(1.07) saturate(.96);
  mask-image:linear-gradient(0deg,black 0 78%,transparent 100%);
}
.concept-booking>div:first-child .button{position:relative;z-index:3}

/* Present the owner/store actions as a readable control strip over the Merch hero. */
.concept-merch-heading>.shop-tools{
  width:max-content;max-width:100%;padding:8px 12px;margin-top:18px;
  border:1px solid rgba(77,109,130,.66);background:rgba(3,10,16,.76);
  backdrop-filter:blur(6px);gap:18px;
}
.concept-merch-heading>.shop-tools a{margin:0;padding:7px 2px;font-size:10px}

/* Page-specific mobile crops retain visible artwork without competing with copy. */
@media(max-width:820px){
  .shows-native-art{width:112%;right:-30%;opacity:.63;object-position:59% 52%}
  .band-native-art{width:108%;right:-43%;opacity:.48;object-position:51% 48%}
  .concept-booking>div:first-child{padding-bottom:205px}
  .concept-booking .booking-native-art{left:-8%;width:116%;height:230px;opacity:.56;object-position:42% 68%}
  .concept-merch>.merch-native-art,.concept-merch-heading>.merch-native-art{width:116%;right:-34%;opacity:.58;object-position:56% 48%}
  .concept-merch-heading>.shop-tools{width:100%;display:flex;flex-wrap:wrap;justify-content:flex-start;gap:6px 18px}
}
@media(max-width:600px){
  .shows-native-art{width:124%;right:-34%;opacity:.55;object-position:58% 54%}
  .band-native-art{width:124%;right:-52%;opacity:.40;object-position:50% 48%}
  .concept-booking>div:first-child{padding-bottom:185px}
  .concept-booking .booking-native-art{left:-10%;width:120%;height:210px;opacity:.52;object-position:42% 70%}
  .concept-merch>.merch-native-art,.concept-merch-heading>.merch-native-art{width:126%;right:-39%;opacity:.52;object-position:56% 48%}
  .concept-merch-heading>.shop-tools{padding:8px 10px;margin-top:16px}
}
'''
css_path.write_text(css, encoding="utf-8")

status_path = Path("docs/CONCEPT_REBUILD_STATUS.md")
status = status_path.read_text(encoding="utf-8")
status = status.replace(
    "- Final crop, lighting and contrast tuning after screenshot review",
    "- Dedicated page crops, stacking, lighting and mobile visibility tuned after screenshot review",
)
status_path.write_text(status, encoding="utf-8")
