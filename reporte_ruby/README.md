**Módulo de Reportes (Rails)**

- **Endpoint:** `GET /api/metrics/cursos` (JSON)
- **Descripción:** Devuelve métricas por curso: número de inscripciones y total de ingresos (en `amount_cents`) sólo de pagos con `status = 'approved'`.
- **Requisitos:** La aplicación espera encontrar las tablas `courses`, `enrollments` y `payments` en la base de datos. Si no existen, la respuesta incluirá un mensaje explicando que faltan tablas.

Ejemplo de respuesta:

**Módulo de Reportes (Rails)**

Resumen
- **Propósito:** Exponer un endpoint que entrega métricas agregadas por curso (inscripciones e ingresos) para análisis.
- **Ubicación del código:** `app/controllers/api/metrics_controller.rb` y `app/services/reports/courses_metrics_service.rb`.

Endpoints
- `GET /api/metrics/cursos` — Devuelve JSON con métricas por curso.

Formato de respuesta
- Respuesta exitosa (HTTP 200):

```
{
  "total_courses": 3,
  "courses": [
    {"id": 1, "title": "Ruby Básico", "enrollments": 12, "income_cents": 120000},
    {"id": 2, "title": "Rails Avanzado", "enrollments": 8, "income_cents": 80000}
  ]
}
```

- Error/BD no disponible (HTTP 200 con mensaje informativo o HTTP 500 si ocurre una excepción):

```
{
  "total_courses": 0,
  "courses": [],
  "message": "Expected tables not found: please ensure courses,enrollments,payments exist in DB"
}
```

Lógica implementada
- La implementación consulta la base de datos mediante `ActiveRecord::Base.connection` para evitar dependencias con modelos AR que podrían no existir en proyectos externos.
- Para cada curso se calculan:
  - `enrollments`: conteo de filas en `enrollments` con `course_id`
  - `income_cents`: suma de `amount_cents` en la tabla `payments` filtrando `status = 'approved'`

Esquema de datos esperado (sólo columnas necesarias)
- `courses`:
  - `id` (integer)
  - `title` o `name` (string)
- `enrollments`:
  - `id`
  - `course_id` (integer)
  - `student_id` (integer)
- `payments`:
  - `id`
  - `course_id` (integer)
  - `amount_cents` (integer)
  - `status` (string) — se considera `approved`

Si tu esquema usa columnas diferentes, ajusta la consulta en `app/services/reports/courses_metrics_service.rb`.

Ejemplos de uso
- Curl (local):

```powershell
curl http://localhost:3000/api/metrics/cursos
```

- Con `httpie`:

```powershell
http GET :3000/api/metrics/cursos
```

Cómo ejecutar localmente (pasos mínimos)
1. Instala dependencias:

```powershell
cd reporte_ruby
bundle install
```

2. Configura la base de datos en `config/database.yml` y crea/migra/seed si lo deseas:

```powershell
rails db:create db:migrate db:seed
```

3. Ejecuta el servidor:

```powershell
rails s -b 0.0.0.0
```

4. Llama al endpoint con `curl` o navegador: `http://localhost:3000/api/metrics/cursos`.

Seed de ejemplo (SQL)
- Si no tienes datos, puedes insertar filas mínimas (ajusta nombres y tipos según tu esquema):

```sql
INSERT INTO courses (id, title, created_at, updated_at) VALUES (1, 'Ruby Básico', NOW(), NOW());
INSERT INTO enrollments (id, course_id, student_id, created_at, updated_at) VALUES (1, 1, 1, NOW(), NOW());
INSERT INTO payments (id, course_id, amount_cents, status, created_at, updated_at) VALUES (1, 1, 120000, 'approved', NOW(), NOW());
```

Pruebas y validación
- No se añadieron pruebas automáticas en esta tarea; sugiero agregar specs en `test/` o `spec/` que:
  - Mocken la conexión a la BD y verifiquen la estructura JSON devuelta.
  - Inserten datos en la DB de prueba y comparen los resultados.

Extensiones sugeridas
- Soportar filtros por rango de fechas (`?from=YYYY-MM-DD&to=YYYY-MM-DD`).
- Agrupaciones por periodo (mensual, trimestral).
- Autenticación y autorización (API token o JWT).
- Página administrativa con gráficos (consumir este endpoint desde Vue/JS).

Seguridad y notas operativas
- No expongas datos de tarjetas ni PAN desde el módulo de reportes.
- El servicio sólo resume `amount_cents` ya existentes en `payments`.
- Asegura permisos mínimos para la cuenta DB usada por la app (SELECT en tablas de interés).

Dónde editar
- Lógica del endpoint: `app/controllers/api/metrics_controller.rb`
- Cálculo de métricas: `app/services/reports/courses_metrics_service.rb`

Contacto
- Si quieres que añada filtros, pruebas automáticas o un endpoint adicional `/api/metrics/resumen`, dime qué métricas adicionales necesitas y lo implemento.
