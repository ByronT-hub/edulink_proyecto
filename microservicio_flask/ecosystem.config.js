module.exports = {
  apps: [
    {
      name: "edulink-flask-1",
      script: "./venv/bin/gunicorn",
      args: "app:app -b 0.0.0.0:5055",
      cwd: __dirname,
      env: {
        FLASK_ENV: "production"
      }
    },
    {
      name: "edulink-flask-2",
      script: "./venv/bin/gunicorn",
      args: "app:app -b 0.0.0.0:5056",
      cwd: __dirname,
      env: {
        FLASK_ENV: "production"
      }
    }
  ]
}

