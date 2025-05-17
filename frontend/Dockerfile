FROM node:alpine

ADD . /frontend

WORKDIR /frontend

RUN npm i && npm run build

FROM nginx:alpine

COPY --from=0 /frontend/dist /usr/share/nginx/html

CMD ["nginx", "-g", "daemon off;"]