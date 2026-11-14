"""Loopback-only static frontend check with a separate, real PHP API.

Serves public files below /MCR-Website/ to check GitHub Pages project paths.
Never serves PHP, database files, credentials, dashboard code or directory lists.
"""
import argparse
import http.server
import json
from pathlib import Path
from urllib.parse import unquote, urlsplit

parser = argparse.ArgumentParser()
parser.add_argument("--port", type=int, required=True)
parser.add_argument("--api-base", default="")
args = parser.parse_args()
root = Path(__file__).resolve().parent.parent
prefix = "/MCR-Website/"
pages = {f"{name}.html" for name in ("index", "about", "music", "tour", "news", "news-detail", "showcase", "ticket-transaction")}
styles = {f"{name}.css" for name in ("base", "home", "about", "music", "tour", "news", "news-detail", "showcase", "ticket-transaction")}
scripts = {"script.js", "content.js"}


class Handler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        path = unquote(urlsplit(self.path).path)
        if not path.startswith(prefix):
            self.send_error(404)
            return
        relative = path[len(prefix):] or "index.html"
        if relative == "site-config.js":
            body = ("window.MCR_CONFIG = Object.freeze(" + json.dumps({"apiBase": args.api_base}) + ");").encode()
            self.send_response(200)
            self.send_header("Content-Type", "text/javascript; charset=utf-8")
            self.send_header("Content-Length", str(len(body)))
            self.end_headers()
            self.wfile.write(body)
            return
        target = (root / relative).resolve()
        public_asset = relative.startswith("assets/") and target.is_relative_to(root / "assets") and target.suffix.lower() in {".png", ".jpg", ".jpeg", ".webp", ".gif", ".svg"}
        if not target.is_file() or (relative not in pages | styles | scripts and not public_asset):
            self.send_error(404)
            return
        self.path = "/" + relative
        super().do_GET()

    def translate_path(self, path):
        return str(root / unquote(urlsplit(path).path).lstrip("/"))


http.server.ThreadingHTTPServer(("127.0.0.1", args.port), Handler).serve_forever()
