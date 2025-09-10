#!/bin/bash
# Advanced health check script

set -e

# Configuration
HEALTH_URL="http://localhost:80/health.php"
MAX_RESPONSE_TIME=5000  # 5 seconds in milliseconds

# Perform health check
response=$(curl -s -w "%{http_code}:%{time_total}" -o /tmp/health_response.json "$HEALTH_URL" || echo "000:999")

# Parse response
http_code=$(echo "$response" | cut -d: -f1)
response_time=$(echo "$response" | cut -d: -f2)
response_time_ms=$(echo "$response_time * 1000" | bc -l | cut -d. -f1)

# Check HTTP status
if [ "$http_code" != "200" ]; then
  echo "Health check failed: HTTP $http_code"
  exit 1
fi

# Check response time
if [ "$response_time_ms" -gt "$MAX_RESPONSE_TIME" ]; then
  echo "Health check failed: Response time too slow (${response_time_ms}ms > ${MAX_RESPONSE_TIME}ms)"
  exit 1
fi

# Parse JSON response for detailed status
if command -v jq >/dev/null 2>&1; then
  status=$(jq -r '.status' /tmp/health_response.json 2>/dev/null || echo "unknown")
  if [ "$status" != "healthy" ]; then
    echo "Health check failed: Status is $status"
    jq '.' /tmp/health_response.json
    exit 1
  fi
fi

echo "Health check passed: HTTP $http_code, ${response_time_ms}ms"
exit 0
