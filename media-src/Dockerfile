FROM node:22

RUN apt-get update && apt-get install -y ffmpeg && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app

RUN npm install --omit=dev

EXPOSE 80 

CMD ["npm", "start"]
