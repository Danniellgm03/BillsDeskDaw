# Usa una imagen base de Node.js Alpine
FROM node:20.18-alpine

# Establece el directorio de trabajo
WORKDIR /var/www/html/frontend/billsdesk

# Copia todos los archivos de billsdesk-vue
COPY ./billsdesk-vue ./

# RUN npm install

# Expone el puerto para la aplicación
EXPOSE 5173

# CMD ["tail", "-f", "/dev/null"]
CMD ["npm", "run", "install-and-run"]

# # Usa una imagen base de Node.js Alpine
# FROM node:20.18-alpine

# # Establece el directorio de trabajo
# WORKDIR /var/www/html/frontend/

# # Copia todos los archivos de billsdesk-vue
# COPY ./billsdesk-vue/ ./ 

# # Cambia a la carpeta billsdesk y ejecuta npm install
# # RUN npm install --prefix billsdesk


# # Expone el puerto para la aplicación
# EXPOSE 5173

# # Comando para mantener el contenedor en ejecución
# CMD ["tail", "-f", "/dev/null"]

