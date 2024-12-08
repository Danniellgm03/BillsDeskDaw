# BillsDeskDaw







Aquí tienes una lista exhaustiva de posibles tests para la clase InvoiceImport. Los tests incluyen casos lógicos, de límite y de error, abarcando tanto lo funcional como lo absurdo.

Categorías de Tests
1. Tests de Mapear Columnas
Verificar que las columnas mapeadas correctamente se transforman en los campos esperados.
Lanzar una excepción si no hay mapeos definidos (column_mappings está vacío).
Comprobar qué ocurre si una columna del CSV no está mapeada en column_mappings.
Verificar que los espacios en los nombres de las columnas del CSV se reemplazan por _.
Comprobar qué pasa si el nombre de la columna tiene caracteres especiales.
2. Tests de Validación de Reglas
Validar que una fila con valores que coinciden con validation_rules se resalta correctamente.
Verificar que no se aplica ningún resaltado si no hay reglas de validación.
Comprobar que las condiciones en validation_rules funcionan con todos los operadores (>, <, >=, <=, ==, !=).
Comprobar que el resaltado de fila (row_highlight) funciona correctamente.
Comprobar que se lanza una excepción si una regla de validación no tiene los campos field, operator o value.
3. Tests de Reglas de Corrección
Verificar que se aplican las correcciones (corrections) cuando las condiciones se cumplen.
Comprobar que no se aplican correcciones si las condiciones no se cumplen.
Validar que update, add, y subtract funcionan correctamente en applyCorrections.
Comprobar que las correcciones funcionan con valores simples (value) y con rangos (min, max).
Probar el manejo de step y step_increment en las correcciones.
Verificar qué ocurre si una corrección apunta a un campo inexistente en la fila.
4. Tests de Duplicados
Comprobar que las filas duplicadas según duplicate_field son marcadas correctamente.
Verificar que las filas no duplicadas no se marcan como duplicadas.
Lanzar una excepción si duplicate_field no existe en las filas del CSV.
Comprobar qué ocurre si todas las filas son duplicadas.
Probar con una mezcla de filas duplicadas y no duplicadas.
5. Tests de Fórmulas
Verificar que se calculan correctamente las columnas nuevas según las fórmulas definidas.
Comprobar qué ocurre si una fórmula utiliza un campo inexistente.
Lanzar una excepción si la fórmula no es válida (ejemplo: error de sintaxis en eval).
Verificar que las columnas calculadas se añaden correctamente a las filas procesadas.
Probar fórmulas más complejas con múltiples operadores matemáticos.
6. Tests de Agregaciones
Verificar que las agregaciones (sum, average) se calculan correctamente para las columnas definidas.
Comprobar qué ocurre si una columna definida en aggregations no existe en las filas.
Validar que las filas agregadas al final tienen los valores calculados correctamente.
Probar con una plantilla sin definiciones de aggregations.
7. Tests de Errores
Lanzar una excepción si el CSV está vacío.
Comprobar qué ocurre si una fila del CSV está completamente vacía.
Lanzar una excepción si una regla tiene un formato incorrecto.
Manejar casos de datos incompletos en las filas (ejemplo: campos nulos o faltantes).
Verificar que no se procesa ninguna fila si el template está vacío.
8. Tests de Rendimiento
Probar con un CSV con 1,000,000 de filas y medir el tiempo de procesamiento.
Verificar que el uso de memoria es razonable con archivos grandes.
Comprobar que el rendimiento es consistente con diferentes configuraciones de reglas.
9. Tests de Edge Cases (Casos Absurdos)
Probar un CSV con columnas vacías como ['', '', ''].
Procesar un CSV donde todas las filas son idénticas.
Procesar un CSV donde todas las columnas son idénticas (ejemplo: todas llamadas peso).
Probar con filas que contienen solo valores nulos.
Comprobar qué ocurre con valores extremos (ejemplo: números muy grandes o negativos).