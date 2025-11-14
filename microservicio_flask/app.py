from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# ====== Endpoint de pago: /api/cards/authorize ======
@app.route("/api/cards/authorize", methods=["POST"])
def authorize_card():
    """
    Versión básica de prueba:
    - Lee el JSON del cuerpo.
    - Siempre aprueba el pago con un auth_code de ejemplo.
    - Luego tú podrás conectar esto a MySQL y validar saldo/estado de tarjeta.
    """
    data = request.get_json(force=True) or {}

    # Solo para pruebas, sin lógica real todavía
    response = {
        "approved": True,
        "auth_code": "TEST1234",
        "message": "Pago simulado aprobado (microservicio en modo demo)."
    }
    return jsonify(response), 200


# ====== Endpoint de validación de certificados: /api/validate/<code> ======
@app.route("/api/validate/<code>", methods=["GET"])
def validate_certificate(code):
    """
    Versión básica de prueba:
    - Devuelve datos simulados de un certificado.
    - Luego lo vas a conectar a tu tabla de certificados en MySQL.
    """
    result = {
        "code": code,
        "estudiante": "Estudiante de prueba",
        "curso": "Curso de ejemplo",
        "fecha_emision": "2025-11-14",
        "valido": True
    }
    return jsonify(result), 200


# Punto de entrada para pruebas locales (python app.py)
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5055, debug=True)
