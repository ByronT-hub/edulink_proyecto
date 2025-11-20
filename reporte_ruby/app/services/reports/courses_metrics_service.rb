module Reports
  class CoursesMetricsService
    # Returns aggregated metrics per course.
    # Safe: works even if the expected tables or AR models are not defined.
    def self.call
      conn = ActiveRecord::Base.connection

      unless conn.table_exists?('courses')
        return {
          total_courses: 0,
          courses: [],
          message: 'Expected tables not found: please ensure courses,enrollments,payments exist in DB'
        }
      end

      courses = conn.exec_query('SELECT id, COALESCE(title, name) AS title FROM courses')

      rows = courses.map do |c|
        course_id = c['id']
        enrollments_count = if conn.table_exists?('enrollments')
                              conn.exec_query("SELECT COUNT(*) AS cnt FROM enrollments WHERE course_id = #{conn.quote(course_id)}").first['cnt'].to_i
                            else
                              0
                            end

        income_cents = if conn.table_exists?('payments')
                         q = "SELECT COALESCE(SUM(amount_cents),0) AS sumc FROM payments WHERE course_id = #{conn.quote(course_id)} AND status = 'approved'"
                         conn.exec_query(q).first['sumc'].to_i
                       else
                         0
                       end

        {
          id: course_id,
          title: c['title'],
          enrollments: enrollments_count,
          income_cents: income_cents
        }
      end

      {
        total_courses: courses.count,
        courses: rows
      }
    end
  end
end
