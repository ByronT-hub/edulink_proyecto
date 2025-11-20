from models.db import get_db
import uuid

def procesar_pago(data):
    try:
        card = data.get("card", {})
        amount = data.get("amount_cents", 0)

        conn = get_db()
        cursor = conn.cursor()

        cursor.execute(
            "SELECT * FROM cards WHERE last4 = %s LIMIT 1",
            (card["pan"][-4:],)
        )
        tarjeta = cursor.fetchone()

        if not tarjeta:
            return {"approved": False, "message": "Tarjeta no encontrada"}

        if int(card["exp_yy"]) < 25:
            return {"approved": False, "message": "Tarjeta expirada"}

        if tarjeta["status"] == "blocked":
            return {"approved": False, "message": "Tarjeta bloqueada"}

        if tarjeta["balance_cents"] < amount:
            return {"approved": False, "message": "Fondos insuficientes"}

        nuevo_saldo = tarjeta["balance_cents"] - amount
        cursor.execute(
            "UPDATE cards SET balance_cents = %s WHERE id = %s",
            (nuevo_saldo, tarjeta["id"])
        )

        auth_code = str(uuid.uuid4())[:8].upper()

        cursor.execute("""
            INSERT INTO authorizations (card_id, amount_cents, auth_code)
            VALUES (%s, %s, %s)
        """, (tarjeta["id"], amount, auth_code))

        conn.commit()

        return {
            "approved": True,
            "auth_code": auth_code,
            "message": "Pago aprobado"
        }

    except Exception as e:
        return {
            "approved": False,
            "message": "Error interno en servicio de pago",
            "error": str(e)
        }
