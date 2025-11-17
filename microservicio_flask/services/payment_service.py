from models.db import get_db
from utils.security import hash_pan
import random, string

def generar_codigo():
    return ''.join(random.choices(string.ascii_uppercase + string.digits, k=10))

def procesar_pago(data):
    db = get_db()
    cursor = db.cursor()

    merchant_ref = data["referencia"]
    amount = int(data["monto_centavos"])
    tarjeta = data["tarjeta"]

    pan_hash = hash_pan(tarjeta["pan"])

    # 1) Buscar tarjeta por hash
    cursor.execute("SELECT * FROM cards WHERE hash_pan=%s", (pan_hash,))
    card = cursor.fetchone()

    if not card:
        return { "approved": False, "message": "Tarjeta no encontrada" }

    # 2) Validar tarjeta activa
    if card["status"] != "active":
        return { "approved": False, "message": "Tarjeta bloqueada" }

    # 3) Validar expiración
    if tarjeta["exp_mm"] != card["exp_mm"] or tarjeta["exp_yy"] != card["exp_yy"]:
        return { "approved": False, "message": "Tarjeta expirada" }

    # 4) Validar saldo
    if card["balance_cents"] < amount:
        return { "approved": False, "message": "Fondos insuficientes" }

    # 5) Descontar saldo
    nuevo_saldo = card["balance_cents"] - amount
    cursor.execute("UPDATE cards SET balance_cents=%s WHERE id=%s", (nuevo_saldo, card["id"]))

    # 6) Registrar autorización
    auth_code = generar_codigo()

    cursor.execute("""
        INSERT INTO authorizations
        (merchant_ref, amount_cents, currency, result, message, auth_code, card_id)
        VALUES (%s, %s, 'GTQ', 'approved', 'Pago aprobado', %s, %s)
    """, (merchant_ref, amount, auth_code, card["id"]))

    db.commit()

    # 7) Respuesta final
    return {
        "approved": True,
        "auth_code": auth_code,
        "message": "Pago aprobado"
    }
