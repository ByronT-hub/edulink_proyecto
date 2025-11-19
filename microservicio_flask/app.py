# ==========================================================
# 🔥 CARGAR VARIABLES DE ENTORNO DEL ARCHIVO .env
# ==========================================================
from dotenv import load_dotenv
load_dotenv()   # <-- ESTO ES LO QUE FALTABA PARA PM2
# Ahora Flask sí usará DB_PAGOS_HOST, PORT, USER, PASS, NAME

from flask import Flask
from flask_cors import CORS
from models.db import get_db, close_db

from controllers.payment_controller import payment_bp
from controllers.certificate_controller import certificate_bp


def create_app():
    app = Flask(__name__)

    # ==========================================================
    # 🔥 ACTIVAR CORS PARA PERMITIR PETICIONES DESDE VUE
    # ==========================================================
    CORS(app, resources={
        r"/*": {
            "origins": [
                "http://localhost:5173",
                "http://127.0.0.1:5173",
                "http://localhost:8000",
                "http://127.0.0.1:8000",
                "*"
            ],
            "methods": ["GET", "POST", "OPTIONS"],
            "allow_headers": ["Content-Type", "Authorization"]
        }
    })

    # Cierre automático de conexión
    app.teardown_appcontext(close_db)

    # Registrar Blueprints
    app.register_blueprint(payment_bp, url_prefix="/api")
    app.register_blueprint(certificate_bp, url_prefix="/api")

    @app.get("/")
    def home():
        return {"message": "Microservicio Flask funcionando"}

    return app


app = create_app()

if __name__ == "__main__":
    # 🔥 LISTO PARA FUNCIONAR EN WSL + WINDOWS + PM2
    app.run(host="0.0.0.0", port=5055, debug=False)
