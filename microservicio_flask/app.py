from flask import Flask
from models.db import get_db, close_db

from controllers.payment_controller import payment_bp
from controllers.certificate_controller import certificate_bp

def create_app():
    app = Flask(__name__)

    # Activar cierre automático de la conexión
    app.teardown_appcontext(close_db)

    # Registrar rutas
    app.register_blueprint(payment_bp, url_prefix="/api")
    app.register_blueprint(certificate_bp, url_prefix="/api")

    @app.get("/")
    def home():
        return {"message": "Microservicio Flask funcionando"}

    return app

app = create_app()

if __name__ == "__main__":
    app.run(port=5055, debug=True)
