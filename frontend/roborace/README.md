# RoboRace (frontend)
Monorepo npm workspaces: 8 aplikasi Vite + React 19 + Tailwind 4 di `apps/`, tipe bersama di `packages/contracts`.
```
npm install
npm run dev:publik   # :5170   | dev:auth :5171 | dev:coach :5172 | dev:event :5173
npm run dev:juri     # :5174   | dev:operator :5175 | dev:chief :5176 | dev:super :5177
npm run build        # type-check + build semua app
```
Semua data masih mock di `src/data.ts` tiap app. Baca `HANDOFF.md` sebelum menyambung backend.
