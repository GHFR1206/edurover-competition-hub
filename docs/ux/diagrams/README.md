# Diagram PlantUML

Tiga diagram model sistem dalam format PlantUML:

| File | Isi |
|---|---|
| `use-case.puml` | Use case 7 peran + robot (telemetri), relasi include/extend, prasyarat publish |
| `activity.puml` | Alur end-to-end: Registrasi → Bertanding → Dinilai → Disahkan → Dipublikasi (swimlane per aktor) |
| `class.puml` | Class diagram domain model (mirror migration + model Fase 0), enums, relasi, catatan |

## Render

```bash
# PNG
docker run --rm -v "$PWD":/d plantuml/plantuml:latest -tpng /d/use-case.puml /d/activity.puml /d/class.puml

# SVG
docker run --rm -v "$PWD":/d plantuml/plantuml:latest -tsvg /d/use-case.puml /d/activity.puml /d/class.puml
```

Atau tempel isi file ke https://www.plantuml.com/plantuml.

Preview PNG sudah disertakan di folder ini.

## Catatan konvensi

- **7 peran** saja: Super Admin, Event Admin, Operator Arena, Juri, Chief Judge, Coach/Sekolah, Publik.
- Robot EduRover = aktor teknis khusus telemetri, ditandai abu-abu, flag default **OFF**, tidak masuk skor.
- Istilah kode/identifier berbahasa Inggris; label diagram berbahasa Indonesia.
- Garis `..>` = dependency/include/extend; `*--` = komposisi; `--` = asosiasi.
