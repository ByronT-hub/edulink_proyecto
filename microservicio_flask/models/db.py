import pymysql
import os
from dotenv import load_dotenv

load_dotenv()

def get_db():
    return pymysql.connect(
        host=os.getenv("DB_PAGOS_HOST", "192.168.1.35"),
        port=int(os.getenv("DB_PAGOS_PORT", "3307")),
        user=os.getenv("DB_PAGOS_USER", "flask_user"),
        password=os.getenv("DB_PAGOS_PASSWORD", "admin"),
        database=os.getenv("DB_PAGOS_NAME", "edulink_payments_db"),
        cursorclass=pymysql.cursors.DictCursor,
    )

def close_db(e=None):
    pass
