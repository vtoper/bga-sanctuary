import fitz
from PIL import Image
import os

PDF_FILE = "AN_Family_Hexes_Print_WithoutText.pdf"
NOMS_FILE = "name.txt"
OUTPUT_DIR = "tiles"

os.makedirs(OUTPUT_DIR, exist_ok=True)

with open(NOMS_FILE, "r", encoding="utf-8") as f:
    noms = [ligne.strip() for ligne in f if ligne.strip()]

pdf = fitz.open(PDF_FILE)

if len(pdf) != len(noms):
    raise ValueError(
        f"Nombre de pages ({len(pdf)}) différent du nombre de noms ({len(noms)})"
    )

for i, page in enumerate(pdf):

    pix = page.get_pixmap(
        matrix=fitz.Matrix(3, 3),
        alpha=False
    )

    img = Image.frombytes(
        "RGB",
        [pix.width, pix.height],
        pix.samples
    )

    nom = "".join(
        "_" if c in '<>:"/\\|?*' else c
        for c in noms[i]
    )

    img.save(
        os.path.join(OUTPUT_DIR, f"{nom}.jpg"),
        "JPEG",
        quality=95
    )

    print(f"Page {i+1} -> {nom}.jpg")
    

print("Conversion terminée.")