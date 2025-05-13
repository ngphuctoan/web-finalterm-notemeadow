notemeadow :potted_plant:
=========================

A minimal note-taking app with rich text support, image uploads, tags organisation, sharing and real-time collaboration.

## Development

Spin up the services (MySQL database, y-websocket and the backend itself):

```bash
docker compose -p notemeadow-dev -f compose.yml -f compose.dev.yml up -d
```

Then start the Parcel dev server for the frontend:

```bash
cd frontend

npm i
npm start
```

After that, the app is now accessible at [localhost:1234](http://localhost:1234)!

## Production

To build the app for production:

```bash
docker compose -p notemeadow-prod -f compose.yml -f compose.prod.yml up
```

## Hosting

When hosting the app, it is necessary to configure the environment variables:

### Backend

- `CLIENT_URL`: Frontend URL

- `DB_HOST`: MySQL server host

- `DB_NAME`: MySQL database name

- `DB_USER`: MySQL username

- `DB_PASS`: MySQL password

### Frontend

- `API_URL`: Backend URL

- `WS_URL`: y-websocket URL