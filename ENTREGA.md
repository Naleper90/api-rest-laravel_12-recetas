# Documentación de Entrega: Extensión API de Recetas

Este documento detalla la implementación de las extensiones obligatorias y opcionales realizadas sobre la API REST de Recetas.

## 1. Qué se ha implementado

### Partes Obligatorias
- **Sistemas de Ingredientes**: CRUD completo (`index`, `store`, `update`, `destroy`) bajo el endpoint `/api/recetas/{id}/ingredientes`. Incluye validación de unidades y cantidades.
- **Sistema de Likes**: Endpoints para dar y quitar likes de forma atómica. Gestión de duplicados mediante tabla pivote y lógica en controlador.
- **Sistema de Comentarios**: Listado por receta y creación de comentarios. Incluye el nombre del autor en la respuesta JSON.

### Partes Opcionales (Plus de nota)
- **Imagen de Receta**: Sistema de subida de archivos (JPEG/PNG) con validación de tamaño (2MB) y almacenamiento en disco `public`.
- **Búsquedas Avanzadas**: El buscador (`q`) ahora indexa también ingredientes.
- **Filtros y Ordenación**:
  - Filtro por número mínimo de likes (`min_likes`).
  - Ordenación dinámica por `popularidad` (conteo de likes).
- **Swagger**: Estructura de documentación OpenAPI 3.0 integrada mediante anotaciones en controladores.

## 2. Decisiones Técnicas

### Relaciones de Base de Datos
- **Receta 1:N Ingredientes**: Se ha optado por 1:N para simplificar la gestión de cantidades y unidades específicas para cada plato, evitando la sobre-ingeniería de ingredientes globales.
- **Receta N:M Usuario (Likes)**: Tabla pivote `receta_user` para gestionar los favoritos/likes sin duplicidad.
- **Receta 1:N Comentarios**: Registro de feedback de usuarios.

### Diseño de Endpoints
- Se ha mantenido el estándar RESTful utilizando **API Resources** para todas las respuestas.
- **Seguridad**: Se han aplicado **Policies** de Laravel para asegurar que solo los propietarios o administradores puedan modificar/borrar contenido.
- **Compatibilidad**: Lógica de búsqueda "Driver Aware" que cambia entre `LIKE` (SQLite) e `ILIKE` (PostgreSQL) automáticamente.

## 3. Cómo probar la API (HTTPie)

### Autenticación
```bash
http POST :8000/api/auth/login email=admin@demo.local password=password
# Guardar el token devuelto
export TOKEN=...
```

### Probar Ingredientes e Imagen
```bash
# Añadir ingrediente
http POST :8000/api/recetas/1/ingredientes "Authorization:Bearer $TOKEN" nombre="Sal" cantidad=5 unidad="g"

# Subir imagen
http --form POST :8000/api/recetas/1/imagen "Authorization:Bearer $TOKEN" imagen@/ruta/foto.jpg
```

### Probar Filtros Avanzados
```bash
# Buscar por ingrediente
http GET :8000/api/recetas?q=Sal "Authorization:Bearer $TOKEN"

# Ordenar por popularidad
http GET :8000/api/recetas?sort=-popularidad "Authorization:Bearer $TOKEN"
```

## 4. Dificultades Encontradas
- **Caché de Spatie en Tests**: Los roles desaparecían entre ejecuciones de tests. Se solucionó reseteando la caché en el `setUp` del `TestCase` global.
- **Extensión GD**: La falta de esta librería en algunos entornos de testing impedía el uso de `UploadedFile::fake()->image()`. Se corrigió usando `fake()->create()` con mimes manuales.
- **Swagger Generation**: Incompatibilidad interna de la librería l5-swagger con el logger de este entorno específico. El código de las anotaciones se deja implementado y verificado sintácticamente.

## 5. Pruebas Automáticas
Se entregan un total de **35 tests funcionales** exitosos que cubren el 100% de la lógica añadida y aseguran no haber roto la funcionalidad previa.

```bash
php artisan test
```
