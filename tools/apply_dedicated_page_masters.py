#!/usr/bin/env python3
"""Wire the approved dedicated page artwork into the public site."""
from pathlib import Path


def replace_once(text: str, old: str, new: str, label: str) -> str:
    if old not in text:
        raise RuntimeError(f"Expected {label} target was not found")
    return text.replace(old, new, 1)


sections_path = Path("views/public/sections.php")
sections = sections_path.read_text(encoding="utf-8")
sections = replace_once(
    sections,
    '<img src="<?=e(app_url(\'images/spiritual-gorillaz-home-hero.png\'))?>" width="2288" height="1824" class="shows-native-art" alt="" aria-hidden="true">',
    '<img src="<?=e(app_url(\'images/spiritual-gorillaz-shows-hero.png\'))?>" width="2688" height="1536" class="shows-native-art" alt="" aria-hidden="true">',
    "Shows artwork",
)
sections = replace_once(
    sections,
    '<img class="band-image band-native-art" width="2288" height="1824" src="<?=e(app_url(\'images/spiritual-gorillaz-home-hero.png\'))?>" alt="Spiritual Gorillaz crowned gorilla stage artwork">',
    '<img class="band-image band-native-art" width="2048" height="1170" src="<?=e(app_url(\'images/spiritual-gorillaz-band-hero.png\'))?>" alt="Crowned gorilla and three anonymous musician silhouettes in blue stage light">',
    "Band artwork",
)
sections = replace_once(
    sections,
    '<img class="merch-native-art" width="2288" height="1824" src="<?=e(app_url(\'images/spiritual-gorillaz-home-hero.png\'))?>" alt="" aria-hidden="true">',
    '<img class="merch-native-art" width="2688" height="1536" src="<?=e(app_url(\'images/spiritual-gorillaz-merch-hero.png\'))?>" alt="" aria-hidden="true">',
    "closed Merch artwork",
)
sections = replace_once(
    sections,
    '<a class="button" href="<?=e(app_url(\'press/\'))?>">Explore the press kit ↓</a></div><aside class="details-panel">',
    '<a class="button" href="<?=e(app_url(\'press/\'))?>">Explore the press kit ↓</a><img class="booking-native-art" width="2688" height="1536" src="<?=e(app_url(\'images/spiritual-gorillaz-booking-hero.png\'))?>" alt="" aria-hidden="true"></div><aside class="details-panel">',
    "Booking shell artwork",
)
sections_path.write_text(sections, encoding="utf-8")

booking_path = Path("views/public/booking.php")
booking = booking_path.read_text(encoding="utf-8")
booking = replace_once(
    booking,
    '<img class="booking-native-art" width="1808" height="1280" src="<?=e(app_url(\'images/spiritual-gorillaz-music-hero.png\'))?>" alt="" aria-hidden="true">',
    '<img class="booking-native-art" width="2688" height="1536" src="<?=e(app_url(\'images/spiritual-gorillaz-booking-hero.png\'))?>" alt="" aria-hidden="true">',
    "Booking form artwork",
)
booking_path.write_text(booking, encoding="utf-8")

store_path = Path("views/public/store.php")
store = store_path.read_text(encoding="utf-8")
store = replace_once(
    store,
    "echo '<img class=\"merch-native-art\" width=\"2288\" height=\"1824\" src=\"'.e(app_url('images/spiritual-gorillaz-home-hero.png')).'\" alt=\"\" aria-hidden=\"true\">';",
    "echo '<img class=\"merch-native-art\" width=\"2688\" height=\"1536\" src=\"'.e(app_url('images/spiritual-gorillaz-merch-hero.png')).'\" alt=\"\" aria-hidden=\"true\">';",
    "Merch catalog artwork",
)
store_path.write_text(store, encoding="utf-8")

css_path = Path("assets/css/section-pages.css")
css = css_path.read_text(encoding="utf-8")
marker = "/* === DEDICATED PAGE MASTER ARTWORK === */"
if marker in css:
    raise RuntimeError("Dedicated artwork styles already exist")
css += r'''

/* === DEDICATED PAGE MASTER ARTWORK === */
.shows-native-art{
  right:0;top:0;width:64%;height:100%;object-fit:cover;object-position:60% 52%;
  opacity:.94;filter:brightness(.82) contrast(1.07) saturate(1.08);
  mask-image:linear-gradient(90deg,transparent 0,black 23%,black 100%);
}
.band-native-art{
  right:0;top:0;width:64%;height:100%;max-height:none;object-fit:cover;object-position:58% 50%;
  opacity:.93;filter:brightness(.82) contrast(1.06) saturate(1.05);
  mask-image:linear-gradient(90deg,transparent 0,black 22%,black 100%);
}
.booking-native-art{
  left:-2%;bottom:-2%;width:56%;height:52%;object-fit:cover;object-position:43% 62%;
  opacity:.78;filter:brightness(.76) contrast(1.07) saturate(.96);
  mask-image:linear-gradient(0deg,black 0 76%,transparent 100%);
}
.concept-booking>div:first-child{padding-bottom:210px}
.concept-merch>.merch-native-art,.concept-merch-heading>.merch-native-art{
  right:0;top:0;width:63%;height:100%;object-fit:cover;object-position:62% 50%;
  opacity:.94;filter:brightness(.80) contrast(1.06) saturate(1.05);
  mask-image:linear-gradient(90deg,transparent 0,black 24%,black 100%);
}
.shows-hero:after,.concept-band:after,.concept-merch:after,.concept-merch-heading:after{
  content:"";position:absolute;inset:0;pointer-events:none;z-index:1;
  background:linear-gradient(90deg,rgba(2,7,12,.97) 0,rgba(2,7,12,.72) 37%,rgba(2,7,12,.12) 60%,transparent 76%);
}
.shows-hero>div,.concept-band>div,.concept-merch>div,.concept-merch-heading>div,.concept-merch-heading>.shop-tools{z-index:2}

@media(max-width:820px){
  .shows-native-art{width:98%;right:-38%;opacity:.68;object-position:56% 50%}
  .band-native-art{width:105%;right:-45%;opacity:.50;object-position:50% 48%}
  .booking-native-art{left:-18%;width:86%;height:38%;opacity:.60;object-position:45% 60%}
  .concept-booking>div:first-child{padding-bottom:170px}
  .concept-merch>.merch-native-art,.concept-merch-heading>.merch-native-art{width:108%;right:-47%;opacity:.62;object-position:58% 50%}
}
@media(max-width:600px){
  .shows-native-art{width:126%;right:-62%;opacity:.58}
  .band-native-art{width:130%;right:-66%;opacity:.42}
  .booking-native-art{left:-32%;width:112%;height:34%;opacity:.48}
  .concept-booking>div:first-child{padding-bottom:145px}
  .concept-merch>.merch-native-art,.concept-merch-heading>.merch-native-art{width:132%;right:-68%;opacity:.48}
}
'''
css_path.write_text(css, encoding="utf-8")

test_path = Path("tests/concept_preview.py")
test = test_path.read_text(encoding="utf-8")
needle = """                        page.screenshot(
                            path=str(output / f'concept-{width}-{name}.png'),
                            full_page=True,
                        )
"""
replacement = """                        if name != 'home':
                            selector = '.concept-booking h1' if name == 'booking' else ('.concept-merch-heading h1' if name == 'merch' else '.page-hero h1')
                            for title in page.locator(selector).all():
                                bounds = title.evaluate('''node => {
                                    const box = node.getBoundingClientRect();
                                    const parent = node.parentElement.getBoundingClientRect();
                                    return {left: box.left, right: box.right, parentLeft: parent.left, parentRight: parent.right, scrollWidth: node.scrollWidth, clientWidth: node.clientWidth};
                                }''')
                                if bounds['left'] < bounds['parentLeft'] - 1 or bounds['right'] > bounds['parentRight'] + 1 or bounds['scrollWidth'] > bounds['clientWidth'] + 1:
                                    raise AssertionError(f'{name} title leaves its container at {width}px: {bounds}')
                        page.screenshot(
                            path=str(output / f'concept-{width}-{name}.png'),
                            full_page=True,
                        )
"""
test = replace_once(test, needle, replacement, "concept screenshot block")
test_path.write_text(test, encoding="utf-8")

status_path = Path("docs/CONCEPT_REBUILD_STATUS.md")
status = status_path.read_text(encoding="utf-8")
status = status.replace(
    "- Shows, Band, Booking and Merch now use sharp native artwork treatments while their dedicated masters are reconstructed",
    "- Dedicated native-resolution Shows, Band, Booking and Merch artwork masters are integrated",
)
status = status.replace(
    "## Remaining dedicated artwork reconstruction\n\n- Shows hero master\n- Band hero atmosphere without fabricated member portraits\n- Booking road-case / stage master\n- Merch stage master",
    "## Remaining visual work\n\n- Final crop and contrast tuning after screenshot review\n- Real member portraits and product photography when supplied by the owner",
)
status_path.write_text(status, encoding="utf-8")
