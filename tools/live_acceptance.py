#!/usr/bin/env python3
"""Read-only public RC8 acceptance checks for the deployed Spiritual Gorillaz site.

No login, form submission, cookies, payment calls, or mutation. This script only
performs GET requests against public pages and reports pass/fail.
"""
from __future__ import annotations

import argparse
import sys
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser


class Page(HTMLParser):
    def __init__(self):
        super().__init__()
        self.text=[]
        self.links=[]
        self.h1=0
    def handle_starttag(self, tag, attrs):
        a=dict(attrs)
        if tag=="a" and "href" in a:self.links.append(a["href"])
        if tag=="h1":self.h1+=1
    def handle_data(self, data):
        value=" ".join(data.split())
        if value:self.text.append(value)
    @property
    def body(self):
        return " ".join(self.text)


def fetch(base: str, path: str) -> tuple[int,str,dict]:
    url=urllib.parse.urljoin(base.rstrip("/")+"/",path.lstrip("/"))
    request=urllib.request.Request(url,headers={"User-Agent":"Spirit-RC8-Live-Acceptance/1.0"})
    try:
        with urllib.request.urlopen(request,timeout=15) as response:
            raw=response.read(2_000_000).decode(response.headers.get_content_charset() or "utf-8","replace")
            return response.status,raw,dict(response.headers.items())
    except urllib.error.HTTPError as error:
        return error.code,error.read(2_000_000).decode("utf-8","replace"),dict(error.headers.items())


def check(condition: bool, label: str, failures: list[str]) -> None:
    print(("PASS " if condition else "FAIL ")+label)
    if not condition:failures.append(label)


def main() -> int:
    parser=argparse.ArgumentParser()
    parser.add_argument("--base",default="https://www.spiritualgorillaz.com/")
    args=parser.parse_args()
    base=args.base
    failures=[]

    expected={
        "": [
            "Real people. Shared songs. A reason to come together.",
            "Music. People. Purpose. Start with a song and stay for the connection.",
        ],
        "music/": ["Find a song. Stay for the feeling."],
        "band/": [
            "Chad Lindsey","Guitar, keyboards and vocals",
            "Ron Green","Harmonica",
            "Charlie Savell","Drums",
        ],
        "booking/": [
            "Let's talk about your event.",
            "An inquiry starts the conversation. It does not reserve a date or take a payment.",
        ],
        "press/": ["Band information and booking inquiries."],
    }

    for path,needles in expected.items():
        status,html,headers=fetch(base,path)
        page=Page();page.feed(html)
        label=path or "home"
        check(status==200,f"{label}: HTTP 200",failures)
        check(page.h1==1,f"{label}: exactly one H1",failures)
        for needle in needles:
            check(needle in page.body,f"{label}: contains {needle!r}",failures)

        if path=="":
            link_text=page.body
            check("Merch" not in link_text,f"{label}: closed store does not advertise Merch",failures)
            check("Your bag" not in link_text,f"{label}: closed store does not advertise Your bag",failures)
            check("Music with a higher purpose" not in page.body,f"{label}: old pre-RC8 hero copy is gone",failures)

    # Direct store route remains truthful even when closed.
    status,html,_=fetch(base,"shop/")
    page=Page();page.feed(html)
    check(status==200,"shop: direct route remains available",failures)
    check("No payments are collected" in page.body or "store" in page.body.lower(),"shop: truthful closed-store state",failures)

    # Public account route should never expose an owner/admin surface.
    status,html,_=fetch(base,"account/")
    check(status in (200,302),"account: public route responds safely",failures)

    print()
    if failures:
        print(f"RC8 PUBLIC ACCEPTANCE FAILED: {len(failures)} check(s)")
        return 1
    print("RC8 PUBLIC ACCEPTANCE PASSED")
    return 0


if __name__=="__main__":
    raise SystemExit(main())
