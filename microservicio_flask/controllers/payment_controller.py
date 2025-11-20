from flask import Blueprint, request, jsonify
from services.payment_service import procesar_pago

payment_bp = Blueprint("payment", __name__)

@payment_bp.post("/tarjetas/autorizar")
def autorizar():
    try:
        data = request.get_json(force=True)
        resultado = procesar_pago(data)
        return jsonify(resultado), 200

    except Exception as e:
        return jsonify({
            "approved": False,
            "message": "Error interno en el microservicio",
            "error": str(e)
        }), 500
