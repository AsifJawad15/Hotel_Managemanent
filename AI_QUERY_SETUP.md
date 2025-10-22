# 🤖 AI Query Assistant - Installation & Setup Guide

## SmartStay Natural Language to SQL Feature

This guide will help you set up the AI-powered query assistant that converts natural language to SQL queries using OpenAI's GPT-4.

---

## 📋 Prerequisites

1. **PHP 7.4+** with cURL extension enabled
2. **MySQL 5.7+** or **MariaDB 10.4+**
3. **OpenAI API Key** (you already have this!)
4. **XAMPP** or similar local server (already running)

---

## 🚀 Installation Steps

### Step 1: Import Database Schema

Run the AI assistant database schema file:

```bash
# Using MySQL command line
mysql -u root -p smart_stay < db/09_ai_assistant.sql
```

**OR using phpMyAdmin:**
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select `smart_stay` database
3. Click "Import" tab
4. Choose file: `db/09_ai_assistant.sql`
5. Click "Go"

This will create 3 new tables:
- ✅ `ai_query_history` - Stores all generated queries
- ✅ `ai_query_favorites` - Stores user's favorite queries
- ✅ `ai_usage_stats` - Tracks daily usage and limits

---

### Step 2: Verify Environment Configuration

Your `.env` file is already configured with your API key! Let's verify:

```env
OPENAI_API_KEY=sk-proj-0Fn... (your key is set)
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_MAX_TOKENS=2000
OPENAI_TEMPERATURE=0.1
```

**Security Note:** 
- ✅ `.env` file is in `.gitignore` (won't be committed to Git)
- ⚠️ NEVER share your API key publicly!

---

### Step 3: Test OpenAI API Connection

Let's verify your API key works:

1. Open: `http://localhost/SmartStay/pages/admin/admin_login.php`
2. Login as admin (use your admin credentials)
3. Navigate to: **AI Query Assistant**
4. Try a simple query: "Show all hotels"
5. Click "Generate SQL Query"

**Expected Result:**
- ✅ Query should be generated in ~2-5 seconds
- ✅ You'll see the SQL code and explanation
- ✅ Click "Execute Query" to run it

---

### Step 4: Enable cURL in PHP (if needed)

If you get "cURL not enabled" error:

1. Open: `C:\xampp\php\php.ini`
2. Find line: `;extension=curl`
3. Remove the semicolon: `extension=curl`
4. Save file
5. Restart Apache in XAMPP

---

### Step 5: Add Navigation Link (Optional)

Add link to admin navigation. Open `admin_home.php` and add:

```php
<a href="admin_ai_query.php" class="nav-link">🤖 AI Query Assistant</a>
```

---

## 🎯 Features Implemented

### ✅ Basic Features
- **Natural Language to SQL**: Ask questions in plain English
- **Query Validation**: Only SELECT queries allowed (security)
- **Query History**: Last 10 queries auto-saved
- **Favorites**: Save frequently used queries
- **Usage Tracking**: Monitor API usage and costs

### 🔒 Security Features
- **Read-Only Queries**: No INSERT/UPDATE/DELETE allowed
- **SQL Injection Prevention**: Query validation layer
- **Admin-Only Access**: Requires admin authentication
- **API Key Protection**: Stored in `.env` (not in code)

### 📊 Smart Features
- **Auto-Suggestions**: Quick example queries
- **Execution Timing**: Shows query performance
- **Row Count**: Displays result metrics
- **Explanation**: AI explains what each query does
- **Token Tracking**: Monitors OpenAI API usage

---

## 💡 Example Queries to Try

Copy these into the AI Query Assistant:

### Revenue Analysis
```
What's the total revenue for last month?
Show me top 5 hotels by revenue
Which hotel made the most money this year?
```

### Guest Analytics
```
List top 10 guests by loyalty points
Show platinum members with over 5000 points
How many guests registered this month?
```

### Booking Analysis
```
Show all confirmed bookings for tomorrow
What's the booking cancellation rate?
Which rooms are booked next week?
```

### Hotel Performance
```
Show hotels with 4+ star rating
Which hotel has the most reviews?
List hotels in New York with available rooms
```

### Event Management
```
Show upcoming events this week
Which event has the most participants?
List all wedding events in December
```

---

## 📈 Usage Limits & Costs

### OpenAI API Costs (Approximate)
- **GPT-4 Turbo**: ~$0.01 per query generation
- **GPT-3.5 Turbo**: ~$0.001 per query generation

### Current Configuration
- **Model**: GPT-4 Turbo (most accurate)
- **Max Tokens**: 2000 per request
- **Temperature**: 0.1 (more deterministic)

### Monitoring Usage
Check your usage stats in the AI Query Assistant page or:
```sql
SELECT * FROM ai_usage_stats 
WHERE admin_id = 1 
ORDER BY usage_date DESC 
LIMIT 7;
```

---

## 🔧 Troubleshooting

### Error: "OpenAI API key not configured"
**Solution:** 
- Verify `.env` file exists in `/SmartStay/` folder
- Check `OPENAI_API_KEY` is set correctly
- Restart Apache server

### Error: "cURL Error"
**Solution:**
- Enable cURL extension in `php.ini`
- Restart Apache
- Check firewall isn't blocking outbound HTTPS

### Error: "Invalid API key"
**Solution:**
- Verify your API key at: https://platform.openai.com/api-keys
- Check for extra spaces in `.env` file
- Ensure key starts with `sk-proj-` or `sk-`

### Error: "Rate limit exceeded"
**Solution:**
- You've hit OpenAI's rate limit
- Wait a few minutes and try again
- Consider upgrading OpenAI plan

### Error: "Only SELECT queries allowed"
**Solution:**
- This is a SECURITY FEATURE (working correctly!)
- AI tried to generate INSERT/UPDATE/DELETE
- Rephrase your question to request data viewing only

---

## 📊 Database Schema Reference

### ai_query_history
Stores all AI-generated queries:
- `history_id` - Auto-increment ID
- `admin_id` - Who generated the query
- `natural_query` - Original question
- `generated_sql` - AI-generated SQL
- `execution_status` - Success/Failed/Not Executed
- `tokens_used` - API tokens consumed

### ai_query_favorites
Stores saved favorite queries:
- `favorite_id` - Auto-increment ID
- `admin_id` - Who saved it
- `favorite_name` - User-defined name
- `sql_query` - The SQL query
- `category` - User-defined category
- `use_count` - How many times used

### ai_usage_stats
Daily usage statistics:
- `admin_id` - User ID
- `usage_date` - Date
- `queries_generated` - Count
- `total_tokens` - Total API tokens used

---

## 🎨 Customization Options

### Change AI Model
Edit `.env`:
```env
# For faster/cheaper queries
OPENAI_MODEL=gpt-3.5-turbo

# For more accurate queries (current)
OPENAI_MODEL=gpt-4-turbo-preview
```

### Adjust Response Length
Edit `.env`:
```env
OPENAI_MAX_TOKENS=1000  # Shorter responses
OPENAI_MAX_TOKENS=3000  # Longer responses
```

### Modify Query History Limit
Edit `admin_ai_query.php` line 107:
```php
// Change from 10 to any number
$history_result = $conn->query("SELECT * FROM ai_query_history WHERE admin_id = $admin_id ORDER BY created_at DESC LIMIT 20");
```

---

## 🔐 Security Best Practices

1. **Never commit `.env` to Git** (already in `.gitignore`)
2. **Rotate API keys** periodically
3. **Monitor usage** for unusual activity
4. **Limit admin access** to trusted users only
5. **Regular backups** of query history

---

## 📞 Support & Resources

### OpenAI Resources
- API Documentation: https://platform.openai.com/docs
- API Keys: https://platform.openai.com/api-keys
- Usage Dashboard: https://platform.openai.com/usage

### SmartStay Files
- Main Page: `/pages/admin/admin_ai_query.php`
- OpenAI Helper: `/includes/openai_helper.php`
- Schema Extractor: `/includes/schema_extractor.php`
- Database Schema: `/db/09_ai_assistant.sql`

---

## ✅ Testing Checklist

After installation, test these features:

- [ ] Generate a simple query
- [ ] Execute the generated query
- [ ] View query results
- [ ] Save a query to favorites
- [ ] Use a favorite query
- [ ] Check query history
- [ ] Toggle favorite star in history
- [ ] Try example quick queries
- [ ] Verify security (try "DELETE FROM hotels" - should be blocked)

---

## 🎉 You're All Set!

The AI Query Assistant is now ready to use. Start asking questions in plain English and let AI generate SQL queries for you!

**Quick Start:**
1. Go to: `http://localhost/SmartStay/pages/admin/admin_ai_query.php`
2. Login as admin
3. Type: "Show me all hotels"
4. Click "Generate SQL Query"
5. Click "Execute Query"

**Enjoy your AI-powered database queries!** 🚀
