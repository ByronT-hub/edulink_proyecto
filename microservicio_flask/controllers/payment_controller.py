from flask import Blueprint, request, jsonify
from services.payment_service import procesar_pago

# Blueprint del módulo de pagos
payment_bp = Blueprint("payment", __name__)

# ==========================================================
# 🔥 ENDPOINT PRINCIPAL (ESTE ES EL QUE LLAMA TU FRONTEND)
# ==========================================================
@payment_bp.post("/tarjetas/autorizar")
def autorizar():
    try:
        # Recibir JSON desde Vue
        data = request.get_json(force=True)

        # Procesar pago usando el servicio
        resultado = procesar_pago(data)

        # Devolver respuesta en JSON
        return jsonify(resultado), 200

    except Exception as e:
        return jsonify({
            "approved": False,
            "message": "Error interno en el microservicio",
            "error": str(e)
        }), 500
