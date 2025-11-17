from flask import Blueprint, request, jsonify
from services.payment_service import procesar_pago

payment_bp = Blueprint("payment", __name__)

@payment_bp.post("/tarjetas/autorizar")
def autorizar():
    body = request.json
    resultado = procesar_pago(body)
    return jsonify(resultado)
