# .agents/rag/extract_pdfs.py
import json
import glob
import os
import sys
import pypdf

def extract_all():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    project_root = os.path.abspath(os.path.join(base_dir, "..", ".."))
    ref_dir = os.path.join(project_root, "importacao", "referencia")
    
    pdf_files = sorted(glob.glob(os.path.join(ref_dir, "*.pdf")))
    results = []
    
    for pdf_path in pdf_files:
        filename = os.path.basename(pdf_path)
        try:
            reader = pypdf.PdfReader(pdf_path)
            pages = []
            for idx, page in enumerate(reader.pages):
                text = page.extract_text() or ""
                pages.append({
                    "page_number": idx + 1,
                    "text": text
                })
            results.append({
                "filename": filename,
                "path": pdf_path,
                "total_pages": len(pages),
                "pages": pages
            })
        except Exception as e:
            sys.stderr.write(f"Erro lendo {filename}: {str(e)}\n")
            
    print(json.dumps(results, ensure_ascii=False))

if __name__ == "__main__":
    if sys.platform == "win32":
        sys.stdout.reconfigure(encoding="utf-8")
        sys.stderr.reconfigure(encoding="utf-8")
    extract_all()
