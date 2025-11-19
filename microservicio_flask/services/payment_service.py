from models.db import get_db
from utils.security import hash_pan
import random, string

# ==========================================================
# 🔥 GENERAR CÓDIGO DE AUTORIZACIÓN
# ==========================================================
def generar_codigo():
    return ''.join(random.choices(string.ascii_uppercase + string.digits, k=10))


# ==========================================================
# 🔥 LÓGICA PRINCIPAL DE AUTORIZACIÓN DE TARJETAS
# ==========================================================
def procesar_pago(data):
    db = get_db()
    cursor = db.cursor()

    merchant_ref = data["merchant_ref"]
    amount = int(data["amount_cents"])
    tarjeta = data["card"]

    # Encriptar PAN
    pan_hash = hash_pan(tarjeta["pan"])

    # ----------------------------------------------------------
    # 1) Buscar tarjeta en base de datos
    # ----------------------------------------------------------
    cursor.execute("SELECT * FROM cards WHERE pan_hash=%s", (pan_hash,))
    card = cursor.fetchone()

    if not card:
        return {"approved": False, "message": "Tarjeta no encontrada"}

    # ----------------------------------------------------------
    # 2) Verificar estado de tarjeta
    # ----------------------------------------------------------
    if card["status"] != "active":
        return {"approved": False, "message": "Tarjeta bloqueada"}

    # ----------------------------------------------------------
    # 3) Validación CORRECTA de fecha de expiración
    # ----------------------------------------------------------
    exp_mm_req = int(tarjeta["exp_mm"])
    exp_yy_req = int(tarjeta["exp_yy"])

    exp_mm_db = int(card["exp_mm"])
    exp_yy_db = int(card["exp_yy"])

    if exp_mm_req != exp_mm_db or exp_yy_req != exp_yy_db:
        return {"approved": False, "message": "Tarjeta expirada"}

    # ----------------------------------------------------------
    # 4) Verificar fondos disponibles
    # ----------------------------------------------------------
    if card["balance_cents"] < amount:
        return {"approved": False, "message": "Fondos insuficientes"}

    # ----------------------------------------------------------
    # 5) Descontar saldo si la transacción es válida
    # ----------------------------------------------------------
    nuevo_saldo = card["balance_cents"] - amount
    cursor.execute(
        "UPDATE cards SET balance_cents=%s WHERE id=%s",
        (nuevo_saldo, card["id"])
    )

    # ----------------------------------------------------------
    # 6) Registrar autorización
    # ----------------------------------------------------------
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
