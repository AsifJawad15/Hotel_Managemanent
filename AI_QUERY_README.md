# 🤖 SmartStay AI Query Assistant

## Natural Language to SQL Query Generator

Transform plain English questions into powerful SQL queries using OpenAI's GPT-4 AI technology!

---

## ✨ Features

### 🎯 Core Capabilities
- **Natural Language Processing**: Ask questions in plain English
- **Instant SQL Generation**: Get optimized MySQL queries in seconds
- **Query Execution**: Run generated queries with one click
- **Smart Explanations**: AI explains what each query does
- **Safety First**: Only allows SELECT queries (read-only)

### 📚 History & Favorites
- **Query History**: Automatic tracking of all generated queries
- **Favorites**: Save frequently used queries
- **Quick Access**: Reuse past queries with one click
- **Categories**: Organize favorites by category
- **Usage Stats**: Monitor API usage and costs

### 🔒 Security Features
- **Read-Only Queries**: Prevents data modification
- **SQL Injection Protection**: Built-in validation
- **Admin-Only Access**: Requires authentication
- **API Key Encryption**: Secure .env storage

---

## 🚀 Quick Start

### 1. Access the AI Assistant
```
http://localhost/SmartStay/pages/admin/admin_ai_query.php
```

### 2. Ask a Question
Type your question in plain English:
```
"Show me top 10 hotels by revenue last month"
```

### 3. Generate & Execute
- Click **"Generate SQL Query"**
- Review the generated SQL
- Click **"Execute Query"** to run it
- View results instantly!

---

## 💡 Example Queries

### Revenue & Financial
```
What's the total revenue for last month?
Show me hotels ranked by total revenue
Which month had the highest bookings?
Calculate average booking value by hotel
```

### Guest Analytics
```
List top 10 guests by loyalty points
Show all platinum members
How many new guests registered this month?
Which guests have the most bookings?
```

### Hotel Performance
```
Show hotels with 4+ star rating in New York
Which hotel has the most rooms?
List hotels with available rooms tomorrow
Show hotel occupancy rates
```

### Booking Insights
```
Show all confirmed bookings for next week
What's the cancellation rate by hotel?
List bookings with pending payments
Show check-ins and check-outs for today
```

### Event Management
```
Show upcoming events this week
Which event has the most participants?
List all wedding events in December
Show events by hotel with dates
```

### Reviews & Ratings
```
Show hotels with average rating above 4.5
List recent reviews from last 30 days
Which hotel has the most 5-star reviews?
Show unapproved reviews pending moderation
```

---

## 📂 File Structure

```
SmartStay/
├── .env                              # API key configuration (SECURE!)
├── .env.example                      # Template for .env
├── .gitignore                        # Git ignore rules
├── AI_QUERY_SETUP.md                 # Installation guide
├── AI_QUERY_README.md                # This file
│
├── includes/
│   ├── env_loader.php               # Environment variable loader
│   ├── openai_helper.php            # OpenAI API integration
│   └── schema_extractor.php         # Database schema provider
│
├── pages/admin/
│   └── admin_ai_query.php           # Main AI Query interface
│
└── db/
    └── 09_ai_assistant.sql          # Database tables for AI feature
```

---

## 🗄️ Database Tables

### ai_query_history
Stores all AI-generated queries:
```sql
- history_id          (PK) Auto-increment ID
- admin_id            Foreign key to admins
- natural_query       Original English question
- generated_sql       AI-generated SQL query
- explanation         AI explanation
- execution_status    Success/Failed/Not Executed
- execution_time_ms   Query performance
- rows_returned       Result count
- tokens_used         OpenAI API tokens
- is_favorite         Favorite flag
- created_at          Timestamp
```

### ai_query_favorites
User-saved favorite queries:
```sql
- favorite_id         (PK) Auto-increment ID
- admin_id            Foreign key to admins
- favorite_name       User-defined name
- sql_query           The SQL query
- description         Optional notes
- category            User category
- use_count           Usage counter
- last_used           Last execution time
```

### ai_usage_stats
Daily usage tracking:
```sql
- stat_id             (PK) Auto-increment ID
- admin_id            Foreign key to admins
- usage_date          Date
- queries_generated   Daily count
- queries_executed    Execution count
- total_tokens        Token usage
- successful_queries  Success count
- failed_queries      Error count
```

---

## ⚙️ Configuration

### Environment Variables (.env)

```env
# OpenAI Configuration
OPENAI_API_KEY=your_api_key_here
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_MAX_TOKENS=2000
OPENAI_TEMPERATURE=0.1

# Database
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=smart_stay
```

### Model Options

| Model | Speed | Accuracy | Cost/Query |
|-------|-------|----------|------------|
| gpt-3.5-turbo | ⚡ Fast | ⭐⭐⭐ Good | ~$0.001 |
| gpt-4-turbo-preview | 🐢 Medium | ⭐⭐⭐⭐⭐ Excellent | ~$0.01 |

**Recommendation**: Use `gpt-4-turbo-preview` for best results

---

## 🎨 User Interface

### Main Interface Components

1. **Query Input Area**
   - Large text box for natural language input
   - Quick example buttons
   - Generate button with loading state

2. **Generated Query Display**
   - Syntax-highlighted SQL code
   - AI explanation panel
   - Execute and Save buttons

3. **Results Table**
   - Paginated data grid
   - Row count and execution time
   - Export options (future)

4. **Sidebar**
   - ⭐ Favorites list
   - 🕒 Recent history
   - 📊 Usage statistics

---

## 🔧 API Integration Details

### OpenAI API Call Flow

```
User Question
    ↓
Schema Context Injection
    ↓
GPT-4 Processing
    ↓
JSON Response
    ↓
SQL Extraction
    ↓
Safety Validation
    ↓
Database Execution
```

### Request Structure
```json
{
  "model": "gpt-4-turbo-preview",
  "messages": [
    {
      "role": "system",
      "content": "Database schema + Rules"
    },
    {
      "role": "user",
      "content": "User's natural language query"
    }
  ],
  "temperature": 0.1,
  "response_format": { "type": "json_object" }
}
```

### Response Structure
```json
{
  "sql": "SELECT * FROM hotels...",
  "explanation": "This query retrieves..."
}
```

---

## 🛡️ Security Implementation

### Query Validation
```php
// Only SELECT statements allowed
if (!preg_match('/^SELECT\s+/i', $sql)) {
    throw new Exception('Only SELECT queries allowed');
}

// Block dangerous keywords
$dangerous = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER'];
foreach ($dangerous as $keyword) {
    if (stripos($sql, $keyword) !== false) {
        throw new Exception("Forbidden keyword: $keyword");
    }
}
```

### API Key Protection
- ✅ Stored in `.env` file (not in code)
- ✅ `.env` in `.gitignore` (not committed)
- ✅ Server-side only (never exposed to client)
- ✅ Environment variable isolation

---

## 📊 Usage Analytics

### View Your Stats
```sql
-- Daily usage for last 7 days
SELECT 
    usage_date,
    queries_generated,
    queries_executed,
    total_tokens,
    successful_queries,
    failed_queries
FROM ai_usage_stats
WHERE admin_id = 1
ORDER BY usage_date DESC
LIMIT 7;
```

### Cost Estimation
```sql
-- Approximate cost calculation
SELECT 
    SUM(total_tokens) as total_tokens,
    SUM(total_tokens) * 0.00001 as approx_cost_usd
FROM ai_usage_stats
WHERE admin_id = 1
AND usage_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);
```

---

## 🚨 Troubleshooting

### Common Issues & Solutions

#### 1. "OpenAI API key not configured"
**Cause**: `.env` file missing or API key not set  
**Solution**:
```bash
# Verify .env exists
ls -la .env

# Check content
cat .env | grep OPENAI_API_KEY

# Restart Apache
sudo service apache2 restart
```

#### 2. "cURL Error: Could not resolve host"
**Cause**: No internet connection or DNS issues  
**Solution**:
- Check internet connection
- Verify DNS settings
- Test: `ping api.openai.com`

#### 3. "Query contains forbidden keyword"
**Cause**: AI generated non-SELECT query  
**Solution**:
- Rephrase question to request data viewing
- Example: Instead of "Delete old bookings", ask "Show bookings older than 1 year"

#### 4. "Rate limit exceeded"
**Cause**: Too many API calls in short time  
**Solution**:
- Wait 60 seconds and retry
- Upgrade OpenAI plan for higher limits
- Use query history to avoid regenerating

#### 5. "Invalid JSON response from AI"
**Cause**: AI response format issue  
**Solution**:
- Retry the query
- Simplify the question
- Check OpenAI service status

---

## 💰 Cost Management

### Optimize API Costs

1. **Use Query History**
   - Reuse past queries instead of regenerating
   - Save favorites for frequent queries

2. **Choose Right Model**
   - GPT-3.5 Turbo: Cheaper, good for simple queries
   - GPT-4 Turbo: More expensive, better for complex queries

3. **Set Usage Limits**
   - Monitor daily token usage
   - Set alerts in OpenAI dashboard

4. **Batch Similar Queries**
   - Group related questions
   - Use saved favorites

### Monthly Budget Example
```
Assumptions:
- 100 queries/month
- GPT-4 Turbo @ $0.01/query
- Average 1500 tokens/query

Monthly Cost: ~$10-15 USD
```

---

## 🔮 Future Enhancements

### Planned Features
- [ ] Multi-language support (Bengali, Spanish, etc.)
- [ ] Query result export (CSV, Excel, PDF)
- [ ] Visual query builder
- [ ] Chart/graph generation
- [ ] Scheduled query execution
- [ ] Email report delivery
- [ ] Query performance optimization suggestions
- [ ] Natural language result interpretation

### Advanced Features
- [ ] Voice input integration
- [ ] Query templates library
- [ ] Collaborative query sharing
- [ ] Version control for queries
- [ ] A/B testing for query variations

---

## 📚 Learning Resources

### OpenAI Documentation
- [API Reference](https://platform.openai.com/docs/api-reference)
- [Best Practices](https://platform.openai.com/docs/guides/gpt-best-practices)
- [Pricing](https://openai.com/pricing)

### SQL Resources
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [SQL Tutorial](https://www.w3schools.com/sql/)

---

## 🤝 Contributing

Have ideas to improve the AI Query Assistant? We'd love your input!

### Improvement Areas
- Better prompt engineering
- Enhanced error handling
- UI/UX improvements
- Performance optimizations
- Additional security measures

---

## 📄 License

SmartStay Hotel Management System  
© 2025 All Rights Reserved

---

## 👨‍💻 Support

### Getting Help
1. Check the [Installation Guide](AI_QUERY_SETUP.md)
2. Review common errors above
3. Check OpenAI status: https://status.openai.com
4. Test with simple queries first

### Contact
- GitHub Issues: [Report a bug]
- Documentation: [AI_QUERY_SETUP.md]

---

## 🎉 Success Tips

### For Best Results

1. **Be Specific**
   - ❌ "Show me data"
   - ✅ "Show top 10 hotels by revenue in January 2025"

2. **Use Business Terms**
   - ❌ "Select from bookings table"
   - ✅ "What were the total bookings last month?"

3. **Include Context**
   - ❌ "Show revenue"
   - ✅ "Show monthly revenue comparison for 2024 vs 2025"

4. **Ask for Specific Metrics**
   - ❌ "Hotel performance"
   - ✅ "Show hotel occupancy rate and average rating"

---

**Happy Querying! 🚀**

*Powered by OpenAI GPT-4 Turbo*
