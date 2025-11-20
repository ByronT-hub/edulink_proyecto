module Api
  class MetricsController < ApplicationController
    # GET /api/metrics/cursos
    def cursos
      result = Reports::CoursesMetricsService.call
      render json: result
    rescue => e
      render json: { error: e.message }, status: :internal_server_error
    end
  end
end
