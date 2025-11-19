from flask import Blueprint, request, jsonify
from services.payment_service import procesar_pago

payment_bp = Blueprint("payment", __name__)

# 🔥 ENDPOINT CORRECTO (ESTE ES EL QUE VA A LLAMAR VUE)
@payment_bp.post("/tarjetas/autorizar")
def autorizar():
    data = request.json
    resultado = procesar_pago(data)
    return jsonify(resultado)
