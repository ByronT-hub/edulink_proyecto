from models.db import get_db
from utils.security import hash_pan
import random, string

def generar_codigo():
    return ''.join(random.choices(string.ascii_uppercase + string.digits, k=10))

def procesar_pago(data):
    db = get_db()
    cursor = db.cursor()

    merchant_ref = data["merchant_ref"]
    amount = int(data["amount_cents"])
    tarjeta = data["card"]

    pan_hash = hash_pan(tarjeta["pan"])

    # 1) Buscar tarjeta correcta
    cursor.execute("SELECT * FROM cards WHERE pan_hash=%s", (pan_hash,))
    card = cursor.fetchone()

    if not card:
        return {"approved": False, "message": "Tarjeta no encontrada"}

    # 2) Tarjeta activa
    if card["status"] != "active":
        return {"approved": False, "message": "Tarjeta bloqueada"}

    # 3) Expiración
    if tarjeta["exp_mm"] != card["exp_mm"] or tarjeta["exp_yy"] != card["exp_yy"]:
        return {"approved": False, "message": "Tarjeta expirada"}

    # 4) Fondos suficientes
    if card["balance_cents"] < amount:
        return {"approved": False, "message": "Fondos insuficientes"}

    # 5) Actualizar saldo
    nuevo_saldo = card["balance_cents"] - amount
    cursor.execute(
        "UPDATE cards SET balance_cents=%s WHERE id=%s",
        (nuevo_saldo, card["id"])
    )

    # 6) Registrar autorización (según tu tabla)
    auth_code = generar_codigo()
    cursor.execute(
        "INSERT INTO authorizations (card_id, amount_cents, auth_code) VALUES (%s, %s, %s)",
        (card["id"], amount, auth_code)
    )

    db.commit()

    return {
        "approved": True,
        "auth_code": auth_code,
        "message": "Pago aprobado"
    }
