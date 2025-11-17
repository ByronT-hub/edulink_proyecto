import pymysql
import os
from dotenv import load_dotenv

load_dotenv()

def get_db():
    return pymysql.connect(
        host=os.getenv("DB_PAGOS_HOST"),
        port=int(os.getenv("DB_PAGOS_PORT")),
        user=os.getenv("DB_PAGOS_USER"),
        password=os.getenv("DB_PAGOS_PASSWORD"),
        database=os.getenv("DB_PAGOS_NAME"),
        cursorclass=pymysql.cursors.DictCursor,
    )

def close_db(e=None):
    pass
