// Type declarations
declare global {
  interface Window {
    Laravel?: {
      config?: {
        pusher?: {
          key?: string
          cluster?: string
        }
      }
    }
    Pusher?: any
  }
}

// Robust formula evaluator with comprehensive anomaly handling
const evaluateFormulaSecurely = (formula: string, variables: Record<string, number>): number => {
  // Input validation
  if (!formula || typeof formula !== 'string') {
    throw new Error('Invalid formula: must be a non-empty string')
  }
  
  if (!variables || typeof variables !== 'object') {
    throw new Error('Invalid variables: must be an object')
  }
  
  // Normalize input
  formula = formula.trim()
  if (formula.length === 0) {
    throw new Error('Invalid formula: empty formula')
  }
  
  if (formula.length > 1000) {
    throw new Error('Invalid formula: formula too long (max 1000 characters)')
  }
  
  // Validate and sanitize variables
  const sanitizedVariables: Record<string, number> = {}
  for (const [key, value] of Object.entries(variables)) {
    // Check for reserved words that could cause conflicts
    const reservedWords = ['Math', 'eval', 'function', 'constructor', 'prototype', 'window', 'document']
    if (reservedWords.includes(key)) {
      throw new Error(`Invalid variable name: '${key}' is reserved`)
    }
    
    // Ensure variable names are valid identifiers
    if (!/^[a-zA-Z_$][a-zA-Z0-9_$]*$/.test(key)) {
      throw new Error(`Invalid variable name: '${key}' must be a valid identifier`)
    }
    
    // Sanitize numeric values
    const numValue = Number(value)
    if (!isFinite(numValue)) {
      throw new Error(`Invalid variable value: '${key}' must be a finite number`)
    }
    
    // Prevent extremely large numbers that could cause overflow
    if (Math.abs(numValue) > Number.MAX_SAFE_INTEGER) {
      throw new Error(`Invalid variable value: '${key}' is too large`)
    }
    
    sanitizedVariables[key] = numValue
  }
  
  // Replace variables with their values (more robust replacement)
  let processedFormula = formula
  
  // Sort variable names by length (longest first) to prevent partial replacements
  const sortedVarNames = Object.keys(sanitizedVariables).sort((a, b) => b.length - a.length)
  
  for (const varName of sortedVarNames) {
    const value = sanitizedVariables[varName]
    // Use word boundaries and ensure we're not replacing parts of other identifiers
    const regex = new RegExp(`\\b${varName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'g')
    processedFormula = processedFormula.replace(regex, `(${value})`)
  }
  
  // Enhanced security validation
  const securityPattern = /^[0-9+\-*/.() \t\nMath,]+$/
  if (!securityPattern.test(processedFormula)) {
    throw new Error('Invalid formula: contains disallowed characters')
  }
  
  // More comprehensive dangerous pattern detection
  const dangerousPatterns = [
    /eval/i, /function/i, /constructor/i, /prototype/i, 
    /window/i, /document/i, /global/i, /process/i,
    /require/i, /import/i, /export/i, /__/, /this/i,
    /while/i, /for/i, /do/i, /if/i, /else/i, /switch/i,
    /try/i, /catch/i, /throw/i, /new/i, /delete/i,
    /typeof/i, /instanceof/i, /in/i, /with/i, /debugger/i
  ]
  
  if (dangerousPatterns.some(pattern => pattern.test(processedFormula))) {
    throw new Error('Invalid formula: contains potentially dangerous patterns')
  }
  
  // Check for potential division by zero patterns
  if (/\/\s*0\b|\/(.*[+\-*\/].*0\b)|\(\s*0\s*\)(?=\s*[+\-*\/])/.test(processedFormula)) {
    console.warn('Potential division by zero detected in formula')
  }
  
  // Additional syntax validation
  try {
    // Check balanced parentheses
    let parenCount = 0
    for (const char of processedFormula) {
      if (char === '(') parenCount++
      if (char === ')') parenCount--
      if (parenCount < 0) throw new Error('Unbalanced parentheses')
    }
    if (parenCount !== 0) throw new Error('Unbalanced parentheses')
    
    // Check for valid Math function calls
    const mathFunctionPattern = /Math\.\w+/g
    const mathFunctions = processedFormula.match(mathFunctionPattern) || []
    const allowedMathFunctions = [
      'Math.min', 'Math.max', 'Math.round', 'Math.floor', 'Math.ceil',
      'Math.abs', 'Math.pow', 'Math.sqrt', 'Math.log', 'Math.exp',
      'Math.sin', 'Math.cos', 'Math.tan', 'Math.PI', 'Math.E'
    ]
    
    for (const func of mathFunctions) {
      if (!allowedMathFunctions.some(allowed => func.startsWith(allowed))) {
        throw new Error(`Disallowed Math function: ${func}`)
      }
    }
  } catch (syntaxError) {
    throw new Error(`Syntax error in formula: ${syntaxError.message}`)
  }
  
  // Safe evaluation with timeout protection
  try {
    const func = new Function('Math', `
      "use strict";
      const startTime = Date.now();
      const result = ${processedFormula};
      const endTime = Date.now();
      if (endTime - startTime > 100) {
        throw new Error('Formula execution timeout');
      }
      return result;
    `)
    
    const result = func(Math)
    
    // Comprehensive result validation
    if (typeof result !== 'number') {
      throw new Error('Formula evaluation did not return a number')
    }
    
    if (isNaN(result)) {
      throw new Error('Formula evaluation resulted in NaN')
    }
    
    if (!isFinite(result)) {
      throw new Error('Formula evaluation resulted in infinite value')
    }
    
    // Check for reasonable number ranges
    if (Math.abs(result) > Number.MAX_SAFE_INTEGER) {
      throw new Error('Formula result is too large')
    }
    
    return result
  } catch (executionError) {
    throw new Error(`Formula execution error: ${executionError.message}`)
  }
}

// Enhanced known formula evaluator with anomaly handling
const evaluateKnownFormula = (formulaType: string, variables: Record<string, number>): number => {
  // Input validation
  if (!formulaType || typeof formulaType !== 'string') {
    throw new Error('Invalid formula type')
  }
  
  const { value = 0, max = 100, min = 0, target = 50 } = variables || {}
  
  // Validate all variables are finite numbers
  const nums = { value, max, min, target }
  for (const [key, num] of Object.entries(nums)) {
    if (!isFinite(num)) {
      throw new Error(`Invalid ${key}: must be a finite number`)
    }
  }
  
  // Handle division by zero and other edge cases
  const safeDiv = (numerator: number, denominator: number): number => {
    if (Math.abs(denominator) < Number.EPSILON) {
      console.warn(`Division by zero avoided in ${formulaType} calculation`)
      return denominator === 0 ? 0 : (numerator > 0 ? 100 : 0)
    }
    return numerator / denominator
  }
  
  let result: number
  
  switch (formulaType) {
    case 'percentage_of_target':
      result = target === 0 ? 0 : Math.min(100, safeDiv(value, target) * 100)
      break
      
    case 'linear_scale':
      if (max === min) {
        result = value >= max ? 100 : 0
      } else {
        result = Math.min(100, Math.max(0, safeDiv(value - min, max - min) * 100))
      }
      break
      
    case 'inverse_scale':
      if (max === min) {
        result = value <= min ? 100 : 0
      } else {
        result = Math.min(100, Math.max(0, safeDiv(max - value, max - min) * 100))
      }
      break
      
    case 'exponential_scale':
      if (target === 0) {
        result = value === 0 ? 100 : 0
      } else {
        const ratio = safeDiv(value, target)
        result = Math.min(100, Math.pow(Math.abs(ratio), 2) * 100)
      }
      break
      
    case 'threshold_based':
      if (target === 0) {
        result = value === 0 ? 100 : 0
      } else {
        if (value >= target) result = 100
        else if (value >= target * 0.8) result = 80
        else if (value >= target * 0.6) result = 60
        else result = Math.max(0, safeDiv(value, target) * 40)
      }
      break
      
    default:
      throw new Error(`Unknown formula type: ${formulaType}`)
  }
  
  // Final result validation
  if (!isFinite(result)) {
    console.warn(`Non-finite result in ${formulaType}, returning 0`)
    return 0
  }
  
  return Math.max(0, Math.min(100, result))
}

// Enhanced performKPICalculation method
const performKPICalculation = (value: any, kpiConfig: any): number => {
  // Input validation
  if (!kpiConfig || typeof kpiConfig !== 'object') {
    console.warn('Invalid KPI configuration')
    return 0
  }
  
  if (value === null || value === undefined) {
    return 0
  }
  
  // Convert value to number safely
  const numericValue = typeof value === 'number' ? value : parseFloat(String(value))
  if (!isFinite(numericValue)) {
    console.warn('Invalid numeric value for KPI calculation')
    return 0
  }
  
  const calculationMethod = kpiConfig.calculation_method || 'direct_score'
  
  try {
    switch (calculationMethod) {
      case 'direct_score':
        return Math.min(Math.max(numericValue, 0), 100)

      case 'percentage':
        return Math.min(Math.max(numericValue, 0), 100)

      case 'weighted_average':
        return Math.min(Math.max(numericValue, 0), 100)

      case 'formula':
        if (!kpiConfig.scoring_formula) {
          console.warn('No scoring formula provided')
          return 0
        }
        
        const context = {
          value: numericValue,
          max: typeof kpiConfig.target_value === 'number' ? kpiConfig.target_value : 100,
          min: 0,
          target: typeof kpiConfig.target_value === 'number' ? kpiConfig.target_value : 50
        }

        const result = evaluateFormulaSecurely(kpiConfig.scoring_formula, context)
        return Math.min(Math.max(result, 0), 100)

      default:
        console.warn(`Unknown calculation method: ${calculationMethod}`)
        return 0
    }
  } catch (error) {
    console.error(`KPI calculation error for method '${calculationMethod}':`, error.message)
    return 0
  }
}

// Real-time KPI Service Class
export class RealTimeKPIService {
  private static instance: RealTimeKPIService
  private echo: any = null
  private pusher: any = null

  private constructor() {
    this.initializeEcho()
  }

  public static getInstance(): RealTimeKPIService {
    if (!RealTimeKPIService.instance) {
      RealTimeKPIService.instance = new RealTimeKPIService()
    }
    return RealTimeKPIService.instance
  }

  private initializeEcho() {
    try {
      // Only initialize Echo if we have Pusher configuration
      if (!window.Laravel?.config?.pusher?.key || window.Laravel.config.pusher.key === 'your-pusher-key') {
        console.warn('Pusher not configured, real-time features disabled')
        return
      }

      // Import Echo dynamically to avoid SSR issues
      import('laravel-echo').then(({ default: Echo }) => {
        import('pusher-js').then(({ default: Pusher }) => {
          window.Pusher = Pusher
          
          this.echo = new Echo({
            broadcaster: 'pusher',
            key: window.Laravel.config.pusher.key,
            cluster: window.Laravel.config.pusher.cluster || 'mt1',
            wsHost: window.location.hostname,
            wsPort: 6001,
            wssPort: 6001,
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
          })
        }).catch(error => {
          console.warn('Pusher-js import failed:', error)
        })
      }).catch(error => {
        console.warn('Laravel Echo import failed:', error)
      })
    } catch (error) {
      console.warn('Echo initialization failed:', error)
    }
  }

  public calculateKPI(formData: Record<string, any>, kpiConfig: any): number {
    return calculateKPIScore(formData, kpiConfig)
  }

  public subscribeToKPICalculations(callback: (data: any) => void) {
    if (this.echo) {
      this.echo.channel('kpi-calculations')
        .listen('KPIUpdated', callback)
    }
  }

  public subscribeToPerformanceUpdates(callback: (data: any) => void) {
    if (this.echo) {
      this.echo.channel('performance-updates')
        .listen('PerformanceReportUpdated', callback)
    }
  }

  public disconnect() {
    if (this.echo) {
      this.echo.disconnect()
    }
  }
}

export default RealTimeKPIService