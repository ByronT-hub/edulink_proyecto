from flask import Blueprint, jsonify
from services.certificate_service import validar_certificado

certificate_bp = Blueprint("certificate", __name__)

@certificate_bp.get("/validar/<codigo>")
def validar(codigo):
    resultado = validar_certificado(codigo)
    return jsonify(resultado)
