from dotenv import load_dotenv
load_dotenv()

from flask import Flask
from flask_cors import CORS
from models.db import get_db, close_db

from controllers.payment_controller import payment_bp
from controllers.certificate_controller import certificate_bp

def create_app():
    app = Flask(__name__)

    CORS(app, resources={
        r"/*": {
            "origins": "*",
            "methods": ["GET", "POST", "OPTIONS"],
            "allow_headers": ["Content-Type", "Authorization"]
        }
    })

    app.teardown_appcontext(close_db)

    app.register_blueprint(payment_bp, url_prefix="/api")
    app.register_blueprint(certificate_bp, url_prefix="/api")

    @app.get("/")
    def home():
        return {"message": "Microservicio Flask funcionando"}

    return app

app = create_app()

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5055, debug=False)
