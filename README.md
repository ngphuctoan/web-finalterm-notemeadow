Development:

```bash
docker compose -p notemeadow-dev -f compose.yml -f compose.dev.yml up -d

cd frontend

npm i  # Install packages
npm start  # Run Parcel dev server
```

Production:

```bash
docker compose -p notemeadow-prod -f compose.yml -f compose.prod.yml up
```