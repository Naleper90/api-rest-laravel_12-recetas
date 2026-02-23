# Colección de pruebas con HTTPie

Este documento contiene los comandos necesarios para probar las nuevas funcionalidades de la API de Recetas.

> [!NOTE]
> Sustituye `TOKEN` por el token recibido al hacer login (`POST /api/login`).

## 1. Ingredientes

### Listar ingredientes de una receta
```bash
http GET http://localhost/api/recetas/1/ingredientes "Authorization: Bearer TOKEN"
```

### Añadir ingrediente a mi receta
```bash
http POST http://localhost/api/recetas/1/ingredientes \
    "Authorization: Bearer TOKEN" \
    nombre="Sal" \
    cantidad:=5.5 \
    unidad="gramos"
```

### Actualizar ingrediente
```bash
http PUT http://localhost/api/recetas/1/ingredientes/1 \
    "Authorization: Bearer TOKEN" \
    nombre="Sal gorda" \
    cantidad:=10 \
    unidad="gramos"
```

### Eliminar ingrediente
```bash
http DELETE http://localhost/api/recetas/1/ingredientes/1 \
    "Authorization: Bearer TOKEN"
```

## 2. Likes

### Dar Like a una receta
```bash
http POST http://localhost/api/recetas/1/likes \
    "Authorization: Bearer TOKEN"
```

### Quitar Like
```bash
http DELETE http://localhost/api/recetas/1/likes \
    "Authorization: Bearer TOKEN"
```

## 3. Comentarios

### Listar comentarios de una receta
```bash
http GET http://localhost/api/recetas/1/comentarios \
    "Authorization: Bearer TOKEN"
```

### Escribir un comentario
```bash
http POST http://localhost/api/recetas/1/comentarios \
    "Authorization: Bearer TOKEN" \
    texto="¡Esta receta es fantástica!"
```

### Eliminar mi comentario
```bash
http DELETE http://localhost/api/comentarios/1 \
    "Authorization: Bearer TOKEN"
```

## 4. Mejoras Opcionales

### Subir imagen
```bash
http --form POST http://localhost/api/recetas/1/imagen \
    "Authorization: Bearer TOKEN" \
    imagen@/ruta/a/la/foto.jpg
```

### Filtrar por ingredientes o texto (q)
```bash
http GET http://localhost/api/recetas?q=Tomate \
    "Authorization: Bearer TOKEN"
```

### Filtrar por likes mínimos
```bash
http GET http://localhost/api/recetas?min_likes=5 \
    "Authorization: Bearer TOKEN"
```

### Ordenar por popularidad
```bash
http GET http://localhost/api/recetas?sort=-popularidad \
    "Authorization: Bearer TOKEN"
```
