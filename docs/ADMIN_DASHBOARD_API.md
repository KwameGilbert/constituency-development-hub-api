# Admin Dashboard Data API

This document describes the API endpoints for managing admin dashboard data, including announcements, employment jobs, community ideas, and aggregated dashboard metrics.

## Table of Contents
- [Admin Data Endpoints](#admin-data-endpoints)
- [Announcements Endpoints](#announcements-endpoints)
- [Employment Jobs Endpoints](#employment-jobs-endpoints)
- [Community Ideas Endpoints](#community-ideas-endpoints)

---

## Admin Data Endpoints

These endpoints provide aggregated dashboard data for admin users. All endpoints require authentication and admin/web_admin role.

**Base URL:** `/v1/admin/data`

### GET /v1/admin/data/agents
Get all field agents with their performance data.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| limit | number | No | 50 | Maximum number of results |

**Response Example:**
```json
{
  "success": true,
  "message": "Agents data retrieved",
  "data": {
    "agents": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+233242560140",
        "location": "Sefwi Wiawso",
        "avatar": "https://example.com/avatar.jpg",
        "status": "active",
        "role": "agent",
        "reports_submitted": 45,
        "performance": {
          "resolution_rate": 87.5,
          "avg_response_time": "4 hrs"
        }
      }
    ],
    "summary": {
      "total": 25,
      "active": 20,
      "inactive": 5
    }
  }
}
```

---

### GET /v1/admin/data/analytics/charts
Get chart data for analytics dashboard.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Response Example:**
```json
{
  "success": true,
  "message": "Analytics charts data retrieved",
  "data": {
    "issueStatusDistribution": [
      {"status": "resolved", "count": 45},
      {"status": "in_progress", "count": 30},
      {"status": "pending", "count": 15}
    ],
    "monthlyTrends": [
      {"month": "Jan", "year": 2026, "issues": 120, "resolutions": 95},
      {"month": "Feb", "year": 2026, "issues": 135, "resolutions": 110}
    ],
    "categoryDistribution": [
      {"category": "infrastructure", "count": 40, "percentage": 35.5}
    ]
  }
}
```

---

### GET /v1/admin/data/analytics/insights
Get performance insights and community statistics.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Response Example:**
```json
{
  "success": true,
  "message": "Analytics insights data retrieved",
  "data": {
    "topPerformers": [
      {
        "id": 1,
        "name": "John Doe",
        "avatar": null,
        "issues_resolved": 45,
        "resolution_rate": 92.5
      }
    ],
    "communityInsights": [
      {
        "location": "Sefwi Wiawso",
        "issues_reported": 35,
        "avg_resolution_time": "72 hrs"
      }
    ]
  }
}
```

---

### GET /v1/admin/data/analytics/metrics
Get overview metrics for analytics.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Response Example:**
```json
{
  "success": true,
  "message": "Analytics metrics data retrieved",
  "data": {
    "overview": {
      "total_issues": 450,
      "active_staff": 42,
      "total_projects": 45,
      "active_budget": 5000000
    },
    "weekly_changes": {
      "new_issues": 15,
      "resolved_issues": 12,
      "active_users": 38
    }
  }
}
```

---

### GET /v1/admin/data/announcements
Get announcements list from database.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 10 | Items per page |
| status | string | No | - | Filter by status (draft, published, archived) |

---

### GET /v1/admin/data/audit-logs
Get system audit logs.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 50 | Items per page |

**Response Example:**
```json
{
  "success": true,
  "message": "Audit logs data retrieved",
  "data": {
    "logs": [
      {
        "id": 1,
        "user": "admin@example.com",
        "action": "user.update",
        "resource": "User",
        "resource_id": 15,
        "ip_address": "192.168.1.1",
        "status": "success",
        "timestamp": "2026-01-05T10:30:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 50,
      "total": 1250,
      "total_pages": 25
    }
  }
}
```

---

### GET /v1/admin/data/employment-jobs
Get employment job listings from database.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 20 | Items per page |
| status | string | No | - | Filter by status |
| category | string | No | - | Filter by category |

---

### GET /v1/admin/data/ideas
Get community ideas from database.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 20 | Items per page |
| status | string | No | - | Filter by status |
| category | string | No | - | Filter by category |

---

### GET /v1/admin/data/metrics
Get summary and entity metrics.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Response Example:**
```json
{
  "success": true,
  "message": "Metrics data retrieved",
  "data": {
    "summaryMetrics": [
      {
        "id": "totalIssues",
        "label": "Total Issues",
        "value": 450,
        "subtitle": "12 pending review",
        "icon": "ClipboardList",
        "color": "blue"
      },
      {
        "id": "activeUsers",
        "label": "Active Users",
        "value": 38,
        "subtitle": "45 total registered",
        "icon": "Users",
        "color": "emerald"
      }
    ],
    "entityMetrics": [
      {
        "id": "fieldAgents",
        "label": "Field Agents",
        "count": 25,
        "active": 20
      }
    ]
  }
}
```

---

### GET /v1/admin/data/recent-issues
Get recent issues for dashboard.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| limit | number | No | 10 | Maximum number of issues |

---

### GET /v1/admin/data/all
Get all dashboard data in one request.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Response:** Returns combined data from all static JSON files.

---

## Announcements Endpoints

CRUD operations for managing public announcements.

**Base URL:** `/v1/announcements`

### GET /v1/announcements/public
Get active/published announcements (public).

**Authentication:** Not required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| limit | number | No | 10 | Maximum results |
| category | string | No | - | Filter by category |

**Response Example:**
```json
{
  "success": true,
  "message": "Public announcements retrieved",
  "data": {
    "announcements": [
      {
        "id": 1,
        "title": "Community Meeting Notice",
        "slug": "community-meeting-notice",
        "content": "Join us for the monthly community meeting...",
        "category": "events",
        "priority": "high",
        "status": "published",
        "publish_date": "2026-01-05",
        "expiry_date": "2026-01-15",
        "image": "https://example.com/meeting.jpg",
        "is_pinned": true,
        "views": 245,
        "published_at": "2026-01-05T08:00:00Z",
        "created_at": "2026-01-04T10:00:00Z"
      }
    ]
  }
}
```

---

### GET /v1/announcements/{id}
Get single announcement by ID or slug (public).

**Authentication:** Not required

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | int/string | Announcement ID or slug |

---

### GET /v1/announcements
List all announcements (admin).

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 10 | Items per page |
| status | string | No | - | Filter by status (draft, published, archived) |
| category | string | No | - | Filter by category |
| priority | string | No | - | Filter by priority |

---

### POST /v1/announcements
Create new announcement.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| title | string | Yes | Announcement title |
| content | string | Yes | Announcement content |
| category | string | No | Category (general, events, infrastructure, health, education, emergency, other) |
| priority | string | No | Priority (low, medium, high, urgent) |
| status | string | No | Status (draft, published, archived) |
| publish_date | date | No | Scheduled publish date |
| expiry_date | date | No | Expiry date |
| image | string | No | Image URL |
| attachment | string | No | Attachment URL |
| is_pinned | boolean | No | Pin announcement |

**Request Example:**
```json
{
  "title": "Community Water Project Update",
  "content": "We are pleased to announce the completion of phase 1...",
  "category": "infrastructure",
  "priority": "high",
  "status": "published",
  "is_pinned": true
}
```

---

### PUT /v1/announcements/{id}
Update announcement.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### DELETE /v1/announcements/{id}
Delete announcement.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### POST /v1/announcements/{id}/publish
Publish an announcement.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### POST /v1/announcements/{id}/archive
Archive an announcement.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

## Employment Jobs Endpoints

CRUD operations for managing job listings.

**Base URL:** `/v1/jobs`

### GET /v1/jobs/public
Get open job listings (public).

**Authentication:** Not required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 20 | Items per page |
| category | string | No | - | Filter by category |
| job_type | string | No | - | Filter by job type |

**Response Example:**
```json
{
  "success": true,
  "message": "Public jobs retrieved",
  "data": {
    "jobs": [
      {
        "id": 1,
        "title": "Community Development Officer",
        "slug": "community-development-officer",
        "description": "We are looking for a dedicated...",
        "company": "Constituency Office",
        "location": "Sefwi Wiawso",
        "job_type": "full_time",
        "salary_range": "GHS 3,000 - 5,000",
        "requirements": "Bachelor's degree in Social Work...",
        "responsibilities": "Coordinate community programs...",
        "application_deadline": "2026-02-15",
        "category": "social_services",
        "experience_level": "mid",
        "is_featured": true,
        "applicants_count": 25
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 15,
      "total_pages": 1
    }
  }
}
```

---

### GET /v1/jobs/{id}
Get single job by ID or slug (public).

**Authentication:** Not required

---

### GET /v1/jobs
List all jobs (admin).

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 20 | Items per page |
| status | string | No | - | Filter by status |
| category | string | No | - | Filter by category |
| job_type | string | No | - | Filter by job type |
| experience_level | string | No | - | Filter by experience level |
| location | string | No | - | Search by location |

---

### POST /v1/jobs
Create new job listing.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| title | string | Yes | Job title |
| description | string | Yes | Job description |
| location | string | Yes | Job location |
| company | string | No | Company/organization name |
| job_type | string | No | Job type (full_time, part_time, contract, internship, temporary, volunteer) |
| salary_range | string | No | Salary range text |
| salary_min | number | No | Minimum salary |
| salary_max | number | No | Maximum salary |
| requirements | string | No | Requirements text |
| responsibilities | string | No | Responsibilities text |
| benefits | string | No | Benefits text |
| application_deadline | date | No | Application deadline |
| application_url | string | No | External application URL |
| application_email | string | No | Application email |
| contact_phone | string | No | Contact phone |
| status | string | No | Status (draft, published, closed, archived) |
| category | string | No | Category |
| experience_level | string | No | Experience level (entry, mid, senior, executive) |
| is_featured | boolean | No | Feature job listing |

---

### PUT /v1/jobs/{id}
Update job listing.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### DELETE /v1/jobs/{id}
Delete job listing.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### POST /v1/jobs/{id}/publish
Publish a job listing.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### POST /v1/jobs/{id}/close
Close a job listing.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

## Community Ideas Endpoints

CRUD operations for managing community ideas with voting functionality.

**Base URL:** `/v1/ideas`

### GET /v1/ideas/public
Get approved/implemented ideas (public).

**Authentication:** Not required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 20 | Items per page |
| category | string | No | - | Filter by category |

**Response Example:**
```json
{
  "success": true,
  "message": "Public ideas retrieved",
  "data": {
    "ideas": [
      {
        "id": 1,
        "title": "Community Garden Project",
        "slug": "community-garden-project",
        "description": "A proposal to create community gardens...",
        "category": "environment",
        "submitter_name": "John Doe",
        "status": "approved",
        "priority": "high",
        "votes": 156,
        "estimated_cost": "GHS 10,000 - 15,000",
        "location": "Sefwi Wiawso",
        "target_beneficiaries": "500 households",
        "implementation_timeline": "3 months",
        "images": ["https://example.com/garden1.jpg"],
        "reviewed_at": "2026-01-03T14:00:00Z",
        "created_at": "2026-01-01T10:00:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 45,
      "total_pages": 3
    }
  }
}
```

---

### GET /v1/ideas/top
Get top voted ideas (public).

**Authentication:** Not required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| limit | number | No | 10 | Maximum results |

---

### GET /v1/ideas/{id}
Get single idea by ID or slug.

**Authentication:** Not required

---

### POST /v1/ideas
Submit new community idea.

**Authentication:** Optional (anonymous submissions allowed)

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| title | string | Yes | Idea title |
| description | string | Yes | Idea description |
| category | string | No | Category (infrastructure, education, healthcare, environment, social, economic, governance, other) |
| submitter_name | string | Conditional | Required for anonymous submissions |
| submitter_email | string | Conditional | Required for anonymous submissions |
| submitter_contact | string | No | Contact phone |
| priority | string | No | Priority (low, medium, high) |
| estimated_cost | string | No | Estimated cost text |
| estimated_cost_min | number | No | Minimum estimated cost |
| estimated_cost_max | number | No | Maximum estimated cost |
| location | string | No | Location/area |
| target_beneficiaries | string | No | Who will benefit |
| implementation_timeline | string | No | Estimated timeline |
| images | array | No | Image URLs |
| documents | array | No | Document URLs |

**Request Example:**
```json
{
  "title": "Street Light Installation",
  "description": "Proposal to install solar-powered street lights...",
  "category": "infrastructure",
  "submitter_name": "Jane Smith",
  "submitter_email": "jane@example.com",
  "location": "Asofan Junction",
  "estimated_cost": "GHS 50,000 - 75,000",
  "target_beneficiaries": "2,000 residents",
  "priority": "high"
}
```

---

### POST /v1/ideas/{id}/vote
Vote for an idea.

**Authentication:** Optional (public voting allowed)

**Notes:** 
- Registered users can vote once per idea
- Anonymous votes are tracked by IP address

---

### DELETE /v1/ideas/{id}/vote
Remove vote from an idea.

**Authentication:** Required

---

### GET /v1/ideas
List all ideas (admin).

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | number | No | 1 | Page number |
| limit | number | No | 20 | Items per page |
| status | string | No | - | Filter by status (pending, under_review, approved, rejected, implemented) |
| category | string | No | - | Filter by category |
| priority | string | No | - | Filter by priority |

---

### PUT /v1/ideas/{id}
Update idea (admin).

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### DELETE /v1/ideas/{id}
Delete idea (admin).

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

---

### POST /v1/ideas/{id}/status
Change idea status.

**Authentication:** Required  
**Roles:** `admin`, `web_admin`

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| status | string | Yes | New status (under_review, approved, rejected, implemented) |
| notes | string | No | Admin notes |

**Request Example:**
```json
{
  "status": "approved",
  "notes": "Approved for implementation in Q2 2026"
}
```

---

## Database Tables

### announcements
| Column | Type | Description |
|--------|------|-------------|
| id | int | Primary key |
| created_by | int | FK to web_admins |
| updated_by | int | FK to web_admins |
| title | varchar(255) | Announcement title |
| slug | varchar(255) | URL slug (unique) |
| content | text | Announcement content |
| category | enum | general, events, infrastructure, health, education, emergency, other |
| priority | enum | low, medium, high, urgent |
| status | enum | draft, published, archived |
| publish_date | date | Scheduled publish date |
| expiry_date | date | Expiry date |
| image | varchar(500) | Image URL |
| attachment | varchar(500) | Attachment URL |
| views | int | View count |
| is_pinned | boolean | Pin status |
| published_at | timestamp | Publication timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### employment_jobs
| Column | Type | Description |
|--------|------|-------------|
| id | int | Primary key |
| created_by | int | FK to web_admins |
| updated_by | int | FK to web_admins |
| title | varchar(255) | Job title |
| slug | varchar(255) | URL slug (unique) |
| description | text | Job description |
| company | varchar(255) | Company name |
| location | varchar(255) | Job location |
| job_type | enum | full_time, part_time, contract, internship, temporary, volunteer |
| salary_range | varchar(100) | Salary range text |
| salary_min | decimal(12,2) | Minimum salary |
| salary_max | decimal(12,2) | Maximum salary |
| requirements | text | Requirements |
| responsibilities | text | Responsibilities |
| benefits | text | Benefits |
| application_deadline | date | Application deadline |
| application_url | varchar(500) | External application URL |
| application_email | varchar(255) | Application email |
| contact_phone | varchar(50) | Contact phone |
| status | enum | draft, published, closed, archived |
| category | enum | administration, technical, health, education, social_services, finance, communications, monitoring_evaluation, other |
| experience_level | enum | entry, mid, senior, executive |
| applicants_count | int | Number of applicants |
| views | int | View count |
| is_featured | boolean | Featured status |
| published_at | timestamp | Publication timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### community_ideas
| Column | Type | Description |
|--------|------|-------------|
| id | int | Primary key |
| title | varchar(255) | Idea title |
| slug | varchar(255) | URL slug (unique) |
| description | text | Idea description |
| category | enum | infrastructure, education, healthcare, environment, social, economic, governance, other |
| submitter_name | varchar(255) | Submitter name |
| submitter_email | varchar(255) | Submitter email |
| submitter_contact | varchar(50) | Submitter phone |
| submitter_user_id | int | FK to users (if registered) |
| status | enum | pending, under_review, approved, rejected, implemented |
| priority | enum | low, medium, high |
| votes | int | Vote count |
| estimated_cost | varchar(100) | Estimated cost text |
| estimated_cost_min | decimal(15,2) | Minimum estimated cost |
| estimated_cost_max | decimal(15,2) | Maximum estimated cost |
| location | varchar(255) | Location |
| target_beneficiaries | varchar(255) | Target beneficiaries |
| implementation_timeline | varchar(100) | Timeline |
| images | json | Image URLs |
| documents | json | Document URLs |
| admin_notes | text | Admin notes |
| reviewed_by | int | FK to web_admins |
| reviewed_at | timestamp | Review timestamp |
| implemented_at | timestamp | Implementation timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### community_idea_votes
| Column | Type | Description |
|--------|------|-------------|
| id | int | Primary key |
| idea_id | int | FK to community_ideas |
| user_id | int | FK to users (nullable) |
| voter_ip | varchar(45) | Voter IP address |
| voter_email | varchar(255) | Voter email |
| created_at | timestamp | Vote timestamp |

---

## Status Enums

### Announcement Status
- `draft` - Not published
- `published` - Visible to public
- `archived` - Hidden from public

### Job Status
- `draft` - Not published
- `published` - Open for applications
- `closed` - No longer accepting applications
- `archived` - Hidden from listings

### Idea Status
- `pending` - Awaiting review
- `under_review` - Being reviewed
- `approved` - Approved for implementation
- `rejected` - Not approved
- `implemented` - Successfully implemented
