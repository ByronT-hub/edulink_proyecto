module.exports = {
  apps: [
    {
      name: "edulink-flask",
      script: "app.py",
      interpreter: "/mnt/c/Users/Byron Tobar/Documents/edulink_proyecto/microservicio_flask/venv/bin/python",
      cwd: "/mnt/c/Users/Byron Tobar/Documents/edulink_proyecto/microservicio_flask",
      env: {
        FLASK_ENV: "production",
        PORT: 5055
      }
    }
  ]
}
