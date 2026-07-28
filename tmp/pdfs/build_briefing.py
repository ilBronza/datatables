from __future__ import annotations

import re
import sys
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfbase import pdfmetrics
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    PageBreak,
    PageTemplate,
    Paragraph,
    Spacer,
)


NAVY = colors.HexColor("#17324D")
BLUE = colors.HexColor("#245D82")
RED = colors.HexColor("#A33B2B")
LIGHT_BLUE = colors.HexColor("#EAF2F7")
MID_GREY = colors.HexColor("#65727D")
RULE = colors.HexColor("#CBD5DC")


def register_fonts() -> tuple[str, str, str]:
    regular = Path("/System/Library/Fonts/Supplemental/Arial.ttf")
    bold = Path("/System/Library/Fonts/Supplemental/Arial Bold.ttf")
    italic = Path("/System/Library/Fonts/Supplemental/Arial Italic.ttf")
    if regular.exists() and bold.exists() and italic.exists():
        pdfmetrics.registerFont(TTFont("BriefingSans", regular))
        pdfmetrics.registerFont(TTFont("BriefingSans-Bold", bold))
        pdfmetrics.registerFont(TTFont("BriefingSans-Italic", italic))
        return "BriefingSans", "BriefingSans-Bold", "BriefingSans-Italic"
    return "Helvetica", "Helvetica-Bold", "Helvetica-Oblique"


REGULAR, BOLD, ITALIC = register_fonts()


class BriefingDocTemplate(BaseDocTemplate):
    def __init__(self, filename: str):
        super().__init__(
            filename,
            pagesize=A4,
            leftMargin=17 * mm,
            rightMargin=17 * mm,
            topMargin=20 * mm,
            bottomMargin=16 * mm,
            title="Briefing riunione urbanistica - Santa Maria di Sala",
            author="OpenAI Codex",
        )
        frame = Frame(
            self.leftMargin,
            self.bottomMargin,
            self.width,
            self.height,
            id="main",
        )
        self.addPageTemplates(
            PageTemplate(id="briefing", frames=[frame], onPage=draw_page)
        )


def draw_page(canvas, doc) -> None:
    width, height = A4
    canvas.saveState()
    canvas.setStrokeColor(RULE)
    canvas.setLineWidth(0.6)
    canvas.line(18 * mm, height - 14 * mm, width - 18 * mm, height - 14 * mm)
    canvas.setFont(BOLD, 7.4)
    canvas.setFillColor(NAVY)
    canvas.drawString(
        18 * mm,
        height - 11 * mm,
        "BRIEFING RIUNIONE - SANTA MARIA DI SALA",
    )
    canvas.setFont(REGULAR, 7.2)
    canvas.setFillColor(MID_GREY)
    canvas.drawRightString(width - 18 * mm, 10 * mm, f"Pagina {doc.page}")
    canvas.drawString(18 * mm, 10 * mm, "Documenti esaminati: proposte n. 30, 31 e 32 - luglio 2026")
    canvas.restoreState()


def styles():
    sample = getSampleStyleSheet()
    return {
        "title": ParagraphStyle(
            "Title",
            parent=sample["Title"],
            fontName=BOLD,
            fontSize=22,
            leading=25,
            textColor=NAVY,
            alignment=TA_LEFT,
            spaceAfter=8 * mm,
        ),
        "h1": ParagraphStyle(
            "H1",
            parent=sample["Heading1"],
            fontName=BOLD,
            fontSize=17,
            leading=20,
            textColor=NAVY,
            spaceBefore=1 * mm,
            spaceAfter=4 * mm,
            keepWithNext=True,
        ),
        "h2": ParagraphStyle(
            "H2",
            parent=sample["Heading2"],
            fontName=BOLD,
            fontSize=11.2,
            leading=13.2,
            textColor=BLUE,
            spaceBefore=2.8 * mm,
            spaceAfter=1.2 * mm,
            keepWithNext=True,
        ),
        "h3": ParagraphStyle(
            "H3",
            parent=sample["Heading3"],
            fontName=BOLD,
            fontSize=9.4,
            leading=11.2,
            textColor=RED,
            spaceBefore=2.1 * mm,
            spaceAfter=1 * mm,
            keepWithNext=True,
        ),
        "body": ParagraphStyle(
            "Body",
            parent=sample["BodyText"],
            fontName=REGULAR,
            fontSize=8.3,
            leading=10.8,
            textColor=colors.HexColor("#202830"),
            alignment=TA_LEFT,
            spaceAfter=1.3 * mm,
            splitLongWords=False,
            allowWidows=0,
            allowOrphans=0,
        ),
        "bullet": ParagraphStyle(
            "Bullet",
            parent=sample["BodyText"],
            fontName=REGULAR,
            fontSize=8.2,
            leading=10.6,
            leftIndent=5 * mm,
            firstLineIndent=-3.4 * mm,
            bulletIndent=1.2 * mm,
            textColor=colors.HexColor("#202830"),
            spaceAfter=0.9 * mm,
            allowWidows=0,
            allowOrphans=0,
        ),
        "number": ParagraphStyle(
            "Number",
            parent=sample["BodyText"],
            fontName=REGULAR,
            fontSize=8.2,
            leading=10.6,
            leftIndent=6 * mm,
            firstLineIndent=-4.6 * mm,
            textColor=colors.HexColor("#202830"),
            spaceAfter=1 * mm,
        ),
        "subtitle": ParagraphStyle(
            "Subtitle",
            parent=sample["BodyText"],
            fontName=REGULAR,
            fontSize=10,
            leading=13,
            textColor=MID_GREY,
            spaceAfter=7 * mm,
        ),
        "note": ParagraphStyle(
            "Note",
            parent=sample["BodyText"],
            fontName=ITALIC,
            fontSize=7.8,
            leading=10,
            textColor=MID_GREY,
            borderColor=RULE,
            borderWidth=0.7,
            borderPadding=5,
            backColor=LIGHT_BLUE,
            spaceBefore=3 * mm,
        ),
    }


def inline_markup(text: str) -> str:
    text = text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
    text = re.sub(r"\*\*(.+?)\*\*", r"<b>\1</b>", text)
    text = re.sub(r"\*(.+?)\*", r"<i>\1</i>", text)
    return text


def parse_markdown(path: Path):
    style = styles()
    story = []
    paragraph_buffer: list[str] = []
    first_h1 = True

    def flush_paragraph() -> None:
        nonlocal paragraph_buffer
        if paragraph_buffer:
            text = " ".join(item.strip() for item in paragraph_buffer)
            selected = style["note"] if text.startswith("Nota:") else style["body"]
            story.append(Paragraph(inline_markup(text), selected))
            paragraph_buffer = []

    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.rstrip()
        stripped = line.strip()

        if stripped == "---PAGE---":
            flush_paragraph()
            story.append(PageBreak())
            continue
        if not stripped:
            flush_paragraph()
            continue
        if stripped.startswith("# "):
            flush_paragraph()
            content = inline_markup(stripped[2:].strip())
            story.append(Paragraph(content, style["title"] if first_h1 else style["h1"]))
            first_h1 = False
            continue
        if stripped.startswith("## "):
            flush_paragraph()
            story.append(Paragraph(inline_markup(stripped[3:].strip()), style["h2"]))
            continue
        if stripped.startswith("### "):
            flush_paragraph()
            story.append(Paragraph(inline_markup(stripped[4:].strip()), style["h3"]))
            continue
        if stripped.startswith("- "):
            flush_paragraph()
            story.append(
                Paragraph(
                    inline_markup(stripped[2:].strip()),
                    style["bullet"],
                    bulletText="-",
                )
            )
            continue
        number_match = re.match(r"^(\d+)\.\s+(.*)$", stripped)
        if number_match:
            flush_paragraph()
            story.append(
                Paragraph(
                    inline_markup(number_match.group(2)),
                    style["number"],
                    bulletText=f"{number_match.group(1)}.",
                )
            )
            continue
        paragraph_buffer.append(stripped)

    flush_paragraph()
    return story


def main() -> None:
    source = Path(sys.argv[1])
    output = Path(sys.argv[2])
    output.parent.mkdir(parents=True, exist_ok=True)
    doc = BriefingDocTemplate(str(output))
    story = parse_markdown(source)
    doc.build(story)


if __name__ == "__main__":
    main()
