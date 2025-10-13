// Performance Management TypeScript Types

export interface PerformanceTemplate {
  id: number;
  name: string;
  description?: string;
  department_id: number;
  created_by: number;
  status: 'draft' | 'active' | 'inactive';
  template_data: TemplateData;
  kpi_config?: KPIConfig[];
  created_at: string;
  updated_at: string;
  department?: Department;
  creator?: User;
  kpi_mappings?: TemplateKPIMapping[];
}

export interface TemplateData {
  fields: FormField[];
  metadata: {
    version: string;
    createdAt: string;
  };
}

export interface FormField {
  id: string;
  type: 'text' | 'textarea' | 'number' | 'rating' | 'select' | 'radio' | 'checkbox' | 'date' | 'file' | 'percentage' | 'currency';
  name: string;
  label: string;
  description?: string;
  required: boolean;
  options?: Record<string, string> | string[];
  validation?: FieldValidation;
  kpiConfig?: FieldKPIConfig;
  weight?: number;
}

export interface FieldValidation {
  required?: boolean;
  min?: number;
  max?: number;
  minLength?: number;
  maxLength?: number;
  pattern?: string;
  custom?: string;
}

export interface FieldKPIConfig {
  isKPI: boolean;
  kpiId?: number;
  weight: number;
  calculationMethod: 'direct_score' | 'weighted_average' | 'formula' | 'percentage';
  scoringFormula?: string;
  targetValue?: number;
  thresholds: {
    excellent: number;
    good: number;
    needsImprovement: number;
  };
}

export interface KPIConfig {
  fieldId: string;
  fieldName: string;
  kpiConfig: FieldKPIConfig;
}

export interface TemplateKPIMapping {
  id: number;
  template_id: number;
  field_name: string;
  kpi_id: number;
  weight: number;
  scoring_formula?: string;
  kpi?: PerformanceKPI;
}

export interface PerformanceReport {
  id: number;
  template_id: number;
  employee_id: number;
  reviewer_id?: number;
  status: 'draft' | 'submitted' | 'under_review' | 'approved' | 'rejected';
  form_data: Record<string, any>;
  calculated_kpis?: KPIResult[];
  overall_score?: number;
  review_period: string;
  review_date: string;
  submitted_at?: string;
  reviewed_at?: string;
  created_at: string;
  updated_at: string;
  template?: PerformanceTemplate;
  employee?: User;
  reviewer?: User;
  kpi_scores?: KPIScore[];
}

export interface KPIResult {
  kpi_id: number;
  kpi_name: string;
  field_value: any;
  calculated_score: number;
  weight_applied: number;
  weighted_score: number;
  threshold: 'excellent' | 'good' | 'needs_improvement' | 'poor';
  threshold_color: string;
  threshold_label: string;
  target_value?: number;
  is_above_target?: boolean;
}

export interface KPIScore {
  id: number;
  report_id: number;
  kpi_id: number;
  field_value: string;
  calculated_score: number;
  weight_applied: number;
  created_at: string;
  kpi?: PerformanceKPI;
}

export interface PerformanceKPI {
  id: number;
  name: string;
  description?: string;
  department_id?: number;
  weight: number;
  target_value?: number;
  calculation_method: 'direct_score' | 'weighted_average' | 'formula' | 'percentage';
  scoring_formula?: string;
  created_at: string;
  updated_at: string;
  department?: Department;
  threshold?: KPIThreshold;
  performance_goals?: PerformanceGoal[];
  kpi_scores?: KPIScore[];
}

export interface KPIThreshold {
  id: number;
  kpi_id: number;
  excellent_min: number;
  good_min: number;
  needs_improvement_min: number;
  created_at: string;
  updated_at: string;
}

export interface PerformanceGoal {
  id: number;
  employee_id: number;
  kpi_id: number;
  target_score: number;
  current_score: number;
  achievement_percentage: number;
  period_start: string;
  period_end: string;
  status: 'active' | 'achieved' | 'missed' | 'cancelled';
  created_at: string;
  updated_at: string;
  employee?: User;
  kpi?: PerformanceKPI;
}

export interface PerformanceAlert {
  id: number;
  type: 'threshold_breach' | 'goal_missed' | 'review_due' | 'submission_overdue';
  employee_id: number;
  kpi_id?: number;
  threshold_type?: 'excellent' | 'good' | 'needs_improvement';
  message: string;
  is_read: boolean;
  triggered_at: string;
  resolved_at?: string;
  created_at: string;
  updated_at: string;
  employee?: User;
  kpi?: PerformanceKPI;
}

export interface PerformanceAggregation {
  id: number;
  employee_id: number;
  department_id?: number;
  period_start: string;
  period_end: string;
  overall_score: number;
  kpi_breakdown: Record<string, any>;
  ranking_position?: number;
  percentile?: number;
  created_at: string;
  updated_at: string;
  employee?: User;
  department?: Department;
}

export interface DashboardSummary {
  total_employees: number;
  submitted_reports: number;
  organization_kpi: number;
  goal_achievement: number;
  trend_direction: 'up' | 'down' | 'stable';
}

export interface DepartmentKPI {
  department: string;
  department_id: number;
  avg_kpi: number;
  employee_count: number;
  goal_achievement: number;
  trend: 'up' | 'down' | 'stable';
  top_kpis: string[];
  needs_improvement: string[];
}

export interface PerformanceTrend {
  month: string;
  date: string;
  average_score: number;
  total_reviews: number;
}

export interface TopPerformer {
  employee_id: number;
  employee_name: string;
  department: string;
  score: number;
  period: string;
  review_date: string;
  template_name: string;
}

export interface PerformanceDistribution {
  excellent: number;
  good: number;
  satisfactory: number;
  needs_improvement: number;
  total: number;
}

export interface KPIBreakdown {
  kpi_id: number;
  kpi_name: string;
  average_score: number;
  total_scores: number;
  max_score: number;
  min_score: number;
  score_range: number;
}

export interface DepartmentSummary {
  department_name: string;
  employee_count: number;
  average_score: number;
  total_reviews: number;
  goal_achievement: number;
  performance_trend: 'up' | 'down' | 'stable';
}

export interface TeamPerformance {
  employee_id: number;
  employee_name: string;
  latest_score?: number;
  latest_period?: string;
  review_date?: string;
  status: 'excellent' | 'good' | 'satisfactory' | 'needs_improvement' | 'no_review';
}

export interface PersonalKPI {
  kpi_id: number;
  kpi_name: string;
  field_name: string;
  field_value: any;
  score: number;
  weight: number;
  weighted_score: number;
  threshold: string;
  threshold_color: string;
  threshold_label: string;
  target_value?: number;
  is_above_target?: boolean;
}

export interface PerformanceHistory {
  review_id: number;
  period: string;
  score: number;
  review_date: string;
  template_name: string;
  status: 'excellent' | 'good' | 'satisfactory' | 'needs_improvement';
}

export interface CurrentGoal {
  goal_id: number;
  kpi_name: string;
  target_score: number;
  current_score: number;
  achievement_percentage: number;
  period_start: string;
  period_end: string;
  days_remaining: number;
  progress_percentage: number;
  status: 'active' | 'achieved' | 'missed' | 'cancelled';
  status_color: string;
  achievement_color: string;
}

export interface PeerComparison {
  employee_score: number;
  department_average: number;
  percentile: number;
  ranking: number;
  total_peers: number;
}

export interface ImprovementArea {
  kpi_name: string;
  score: number;
  threshold: string;
  threshold_color: string;
  improvement_needed: number;
}

export interface PendingReport {
  report_id: number;
  template_name: string;
  status: 'draft' | 'submitted';
  created_at: string;
  submitted_at?: string;
  due_date: string;
}

export interface FieldType {
  type: string;
  label: string;
  description: string;
  icon: string;
  defaultConfig: Record<string, any>;
  validation?: FieldValidation;
}

export interface KPIPreview {
  kpi_id: number;
  kpi_name: string;
  field_name: string;
  field_value: any;
  score: number;
  weight: number;
  weighted_score: number;
  threshold: string;
  threshold_color: string;
  threshold_label: string;
  target_value?: number;
  is_above_target?: boolean;
}

export interface TemplateStatistics {
  total_usage: number;
  completed_reviews: number;
  pending_reviews: number;
  average_score: number;
  last_used?: string;
}

export interface KPIStatistics {
  total_scores: number;
  average_score: number;
  max_score: number;
  min_score: number;
  score_distribution: {
    excellent: number;
    good: number;
    needs_improvement: number;
  };
  recent_trend: 'improving' | 'declining' | 'stable';
}

export interface KPIAnalytics {
  performance_over_time: PerformanceTrend[];
  department_breakdown: Array<{
    department: string;
    average_score: number;
    count: number;
  }>;
  top_performers: Array<{
    employee_name: string;
    score: number;
    date: string;
  }>;
  improvement_areas: Array<{
    employee_name: string;
    score: number;
    date: string;
  }>;
}

export interface ReportStatistics {
  total_reports: number;
  draft_reports: number;
  submitted_reports: number;
  approved_reports: number;
  rejected_reports: number;
  average_score: number;
}

// Common interfaces
export interface User {
  id: number;
  first_name: string;
  last_name: string;
  email?: string;
  essentials_department_id?: number;
  department?: Department;
}

export interface Department {
  id: number;
  name: string;
  short_code?: string;
  description?: string;
}

export interface Business {
  id: number;
  name: string;
  currency_id?: number;
}

// API Response types
export interface ApiResponse<T> {
  data: T;
  message?: string;
  success: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

// Form types
export interface FormData {
  [key: string]: any;
}

export interface ValidationError {
  field: string;
  message: string;
}

export interface FormErrors {
  [key: string]: string[];
}

// Chart data types
export interface ChartData {
  labels: string[];
  datasets: ChartDataset[];
}

export interface ChartDataset {
  label: string;
  data: number[];
  backgroundColor?: string | string[];
  borderColor?: string | string[];
  borderWidth?: number;
}

export interface ChartOptions {
  responsive: boolean;
  plugins?: {
    legend?: {
      position?: 'top' | 'bottom' | 'left' | 'right';
    };
    title?: {
      display: boolean;
      text: string;
    };
  };
  scales?: {
    x?: {
      beginAtZero?: boolean;
    };
    y?: {
      beginAtZero?: boolean;
      max?: number;
    };
  };
}

// Permission types
export interface Permission {
  name: string;
  description: string;
}

export interface Role {
  id: number;
  name: string;
  permissions: Permission[];
}

// Event types for real-time updates
export interface PerformanceUpdateEvent {
  type: 'report_submitted' | 'report_approved' | 'kpi_updated' | 'goal_achieved' | 'alert_created';
  data: any;
  timestamp: string;
}

// Export types
export interface ExportOptions {
  format: 'excel' | 'pdf' | 'csv';
  filters?: Record<string, any>;
  date_range?: {
    start: string;
    end: string;
  };
}

export interface ExportResult {
  file_path: string;
  file_name: string;
  file_size: number;
  download_url: string;
}
