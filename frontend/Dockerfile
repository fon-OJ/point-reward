FROM node:20-bookworm

WORKDIR /app

COPY package.json .
COPY vite.config.js .

RUN npm install

COPY . .

EXPOSE 3000
CMD ["npm", "run", "dev"]