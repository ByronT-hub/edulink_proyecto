import pymysql

# ❌ De momento NO usamos .env para probar.
# from dotenv import load_dotenv
# load_dotenv()

def get_pagos_connection():
    return pymysql.connect(
        host="172.18.64.1",
        port=3307,
        user="flask_user",
        password="admon",
        database="edulink_microservicio",
        cursorclass=pymysql.cursors.DictCursor,
    )


def get_app_connection():
    """
    Conexión a la BD principal (Laravel) donde están:
    estudiantes, cursos, inscripciones, certificados.
    """
    return pymysql.connect(
        host="172.18.64.1",
        port=3307,
        user="flask_user",
        password="admon",
        database="edulink_microservicio",
        cursorclass=pymysql.cursors.DictCursor,
    )
