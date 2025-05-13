notemeadow :potted_plant:
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-1-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->
=========================

A minimal note-taking app with rich text support, image uploads, tags organisation, sharing and real-time collaboration.

## Contributors

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/Im-Karl"><img src="https://avatars.githubusercontent.com/u/152507897?v=4?s=100" width="100px;" alt="Im-Karl"/><br /><sub><b>Im-Karl</b></sub></a><br /><a href="#design-Im-Karl" title="Design">🎨</a> <a href="https://github.com/ngphuctoan/web-finalterm-notemeadow/commits?author=Im-Karl" title="Code">💻</a></td>
    </tr>
  </tbody>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

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
