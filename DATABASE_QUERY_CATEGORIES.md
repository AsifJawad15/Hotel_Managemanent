# 🗄️ Database Query Categories Guide

## Overview
The Admin Database Interface (`admin_database.php`) now features **6 comprehensive categories** of SQL queries, organized by operation type. This provides a structured learning and testing environment for database operations.

---

## 📋 Query Categories

### 🏗️ **1. DDL - Data Definition Language**
**Purpose:** Create, alter, and manage database structures

**Queries Included:**
- Show All Tables
- Describe Tables (Hotels, Bookings, Rooms)
- Show CREATE TABLE statements
- Show Indexes
- Show Columns
- Table Statistics (size, row count)
- Foreign Key Relationships

**Use Cases:**
- Understanding database schema
- Analyzing table structure
- Checking constraints and indexes
- Database documentation

---

### ✏️ **2. DML - Data Manipulation Language**
**Purpose:** Select, insert, update, and delete data

**Queries Included:**
- All Active Hotels (ordered by rating)
- Gold Members (loyalty program)
- Upcoming Bookings (confirmed reservations)
- Mid-Range Rooms ($100-$300)
- Events This Month (next 30 days)
- High-Rated Reviews (4+ stars)
- Recent Payments (last 7 days)
- Paid Services

**Use Cases:**
- Retrieving specific data
- Filtering and sorting records
- Date-range queries
- Status-based filtering

---

### 🔍 **3. Subqueries - Nested SELECT Statements**
**Purpose:** Complex queries using nested SELECT statements

**Query Types:**
- **IN Subqueries:** Hotels with Budget Rooms
- **Scalar Subqueries:** Above-Average Loyalty Points
- **Correlated Subqueries:** Hotel Room Statistics, Most Expensive Room per Hotel
- **EXISTS:** Hotels with Upcoming Events
- **NOT EXISTS:** Guests Without Bookings
- **Multiple Subqueries:** Hotel Performance Dashboard

**Use Cases:**
- Comparative analysis
- Finding outliers
- Complex filtering
- Aggregation with conditions

---

### 🔀 **4. Set Operations - UNION, INTERSECT, EXCEPT**
**Purpose:** Combine result sets using set theory

**Queries Included:**
- All System Users (UNION of guests + admins)
- Hotels by Category (rooms/events)
- Customer Segmentation (value tiers)
- Guest Activity Status (booked vs registered)
- Hotels by Tier (premium vs standard)
- Guest Activities (rooms vs events)
- Payment Methods Summary (UNION ALL)
- Business Metrics Dashboard

**Use Cases:**
- Combining disparate data sources
- Creating unified reports
- Categorization and segmentation
- Multi-source analytics

---

### 👁️ **5. Views - Virtual Tables & Perspectives**
**Purpose:** Query existing views and virtual tables

**Views Available:**
- List All Views
- Hotel Summary View
- Booking Details View
- Room Availability View
- Guest History View
- Revenue by Hotel View
- Upcoming Events View
- View Definition (CREATE VIEW)

**Use Cases:**
- Simplified complex queries
- Consistent data perspectives
- Reusable query patterns
- Security and abstraction

---

### 🔗 **6. Joins - Combining Multiple Tables**
**Purpose:** Combine related data from multiple tables

**Join Types Demonstrated:**
- **INNER JOIN (4-way):** Complete Booking Details (guests + rooms + hotels + room_types)
- **LEFT JOIN:** Hotel Performance Report, Top Guest Spenders
- **Mixed Joins:** Event Registration Status (INNER + LEFT)
- **JOIN Chains (5-way):** High-Rated Room Reviews
- **JOIN with Aggregation:** Room Inventory by Type, Payment Transaction History
- **Simple JOIN:** Hotel Services Catalog

**Use Cases:**
- Combining related data
- Complete record views
- Performance analysis
- Transactional reports

---

## 🎨 Visual Design

### Color Coding
Each category has a unique color scheme for easy identification:
- **DDL:** 🔵 Blue (`#3b82f6`)
- **DML:** 🟢 Green (`#059669`)
- **Subqueries:** 🔴 Red (`#dc2626`)
- **Set Operations:** 🟣 Purple (`#7c3aed`)
- **Views:** 🟠 Orange (`#ea580c`)
- **Joins:** 🔷 Cyan (`#0891b2`)

### Interactive Features
- **Click to Execute:** Each query button auto-fills the SQL editor
- **Hover Effects:** Buttons lift and shadow on hover
- **Smooth Scrolling:** Clicking a query scrolls to the editor
- **Result Display:** Tables with scrollable overflow
- **Error Handling:** Clear error messages with red background
- **Success Messages:** Green confirmation with row counts

---

## 📊 Query Complexity Levels

### **Beginner** (Simple SELECT)
- All Active Hotels
- Show All Tables
- Describe Tables
- List All Views

### **Intermediate** (Joins, Aggregations)
- Complete Booking Details (INNER JOIN)
- Hotel Performance Report (LEFT JOIN + GROUP BY)
- Top Guest Spenders
- Room Inventory by Type

### **Advanced** (Subqueries, Set Operations)
- Customer Segmentation (UNION with nested queries)
- Above-Average Loyalty (scalar subquery)
- Most Expensive Room per Hotel (correlated subquery)
- Business Metrics Dashboard (complex UNION ALL)

### **Expert** (Complex Multi-table Joins)
- High-Rated Room Reviews (5-way INNER JOIN)
- Hotel Performance Dashboard (multiple subqueries)
- Payment Transaction History (mixed joins)

---

## 🚀 Usage Tips

### For Learning
1. Start with **DML** category for basic data retrieval
2. Move to **Joins** to understand relationships
3. Explore **Subqueries** for complex filtering
4. Master **Set Operations** for combining data
5. Use **Views** for simplified access
6. Study **DDL** to understand structure

### For Development
1. Test queries in the interface before coding
2. Use **DDL** queries to verify schema changes
3. Use **Views** for commonly accessed data patterns
4. Use **Subqueries** for one-time complex analysis
5. Use **Joins** for comprehensive reports

### For Analysis
1. Start with **Views** for quick insights
2. Use **Joins** for detailed reports
3. Use **Set Operations** for comparative analysis
4. Use **Subqueries** for finding outliers

---

## 🔒 Security Features

### Query Safety
- Read-only SELECT queries emphasized
- No destructive operations (DROP, DELETE, TRUNCATE) in predefined queries
- Admin authentication required
- SQL injection protection via mysqli prepared statements (when used)

### Best Practices
- Always review custom queries before execution
- Use transactions for data modifications
- Backup database before bulk updates
- Test on development environment first

---

## 📈 Performance Considerations

### Query Optimization
- Indexed columns used in WHERE clauses
- LIMIT clauses on large result sets
- Efficient JOIN orders
- Proper GROUP BY usage
- EXISTS vs IN for large datasets

### Monitoring
- Execution time displayed
- Row count shown in results
- Query result size limited (max 500px scroll height)

---

## 🎓 Educational Value

This interface serves as:
1. **Learning Tool:** Examples of all major SQL concepts
2. **Reference Guide:** Copy-paste ready queries
3. **Testing Environment:** Safe query execution
4. **Documentation:** Visual database structure exploration

---

## 🔧 Customization

### Adding New Queries
1. Choose appropriate category section
2. Add button with `onclick="setCustomQuery('...')"` 
3. Use proper CSS class for category (`btn-ddl`, `btn-dml`, etc.)
4. Include title and description in button
5. Test query for correctness

### Creating New Categories
1. Copy category-section div structure
2. Add unique color in style section
3. Create button class variant
4. Add appropriate icon emoji
5. Write descriptive header

---

## 📚 Related Database Features

### Interactive Functions (Separate Section)
- `CalculateAge(date_of_birth)`
- `GetSeason(date)`
- `CalculateDynamicPrice(base_price, check_in, room_type)`

### Stored Procedures (Separate Section)
- `CalculateLoyaltyPoints(booking_id)`
- `CalculateRoomRevenue(hotel_id, start_date, end_date)`
- `GenerateMonthlyHotelReport(hotel_id, year, month)`
- `GetAvailableRooms(hotel_id, check_in, check_out, room_type_id)`
- `ProcessLoyaltyUpgrades()`

---

## 📞 Support

For questions or issues:
- Review query syntax in MySQL documentation
- Check database schema in `db/` folder
- Test queries in smaller chunks
- Use AI Query Assistant for natural language queries

---

**Last Updated:** October 24, 2025  
**Database:** smart_stay  
**Interface Version:** 2.0 - Categorized Edition
