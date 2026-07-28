import math
import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


def main() -> None:
    output = Path(sys.argv[1])
    inputs = [Path(value) for value in sys.argv[2:]]
    columns = 2
    thumb_width = 700
    label_height = 42
    gap = 24

    font = ImageFont.load_default(size=22)
    cells = []
    for path in inputs:
        image = Image.open(path).convert("RGB")
        thumb_height = round(image.height * thumb_width / image.width)
        image = image.resize((thumb_width, thumb_height), Image.Resampling.LANCZOS)
        cells.append((path.name, image))

    cell_height = max(image.height for _, image in cells) + label_height
    rows = math.ceil(len(cells) / columns)
    sheet = Image.new(
        "RGB",
        (
            columns * thumb_width + (columns + 1) * gap,
            rows * cell_height + (rows + 1) * gap,
        ),
        "white",
    )
    draw = ImageDraw.Draw(sheet)

    for index, (label, image) in enumerate(cells):
        row, column = divmod(index, columns)
        x = gap + column * (thumb_width + gap)
        y = gap + row * cell_height
        draw.text((x, y), label, fill="black", font=font)
        sheet.paste(image, (x, y + label_height))

    sheet.save(output, quality=88)


if __name__ == "__main__":
    main()
