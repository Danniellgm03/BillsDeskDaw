import { createI18n } from 'vue-i18n';

// Función para cargar mensajes desde los archivos JSON
function loadLocaleMessages() {
  const locales = import.meta.glob('../locales/*.json', { eager: true }); // Carga los archivos JSON
  const messages = {};

  for (const path in locales) {
    const locale = path.match(/([\w-]+)\.json$/)[1]; // Extrae el nombre del archivo como clave del idioma
    messages[locale] = locales[path].default;
  }

  return messages;
}

const i18n = createI18n({
  locale: 'en', // Idioma predeterminado
  fallbackLocale: 'en', // Idioma de respaldo si no encuentra una traducción
  messages: loadLocaleMessages(), // Carga las traducciones
});

export default i18n;
