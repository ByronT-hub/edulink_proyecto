from models.db import get_db

def validar_certificado(codigo):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
        SELECT c.*, i.estudiante_id, i.curso_id
        FROM certificados c
        JOIN inscripciones i ON i.id = c.inscripcion_id
        WHERE c.codigo = %s
    """, (codigo,))

    cert = cursor.fetchone()

    if not cert:
        return {
            "valido": False,
            "message": "Certificado no encontrado"
        }

    return {
        "valido": True,
        "codigo": cert["codigo"],
        "estudiante_id": cert["estudiante_id"],
        "curso_id": cert["curso_id"],
        "fecha_emision": cert["fecha_emision"]
    }
