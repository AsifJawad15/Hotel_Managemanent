# 🚀 AI Query Assistant - Quick Reference Card

## 🔗 Important URLs

| Page | URL |
|------|-----|
| **Test Connection** | `http://localhost/SmartStay/test_openai_connection.php` |
| **AI Query Page** | `http://localhost/SmartStay/pages/admin/admin_ai_query.php` |
| **Admin Login** | `http://localhost/SmartStay/pages/admin/admin_login.php` |
| **Admin Dashboard** | `http://localhost/SmartStay/pages/admin/admin_home.php` |

---

## 📦 Installation (3 Steps)

### 1️⃣ Import Database
```bash
mysql -u root -p smart_stay < db\09_ai_assistant.sql
```
**OR** use phpMyAdmin → Import → `db/09_ai_assistant.sql`

### 2️⃣ Test Connection
Open: `http://localhost/SmartStay/test_openai_connection.php`  
All lights should be **GREEN** ✅

### 3️⃣ Start Using
Login → Click **"🤖 AI Query"** in navigation → Ask questions!

---

## 💡 Example Queries

### Quick Copy-Paste Examples

#### Revenue Analysis
```
What's the total revenue for last month?
Show me top 5 hotels by revenue
Which hotel made the most money this year?
```

#### Guest Insights
```
List top 10 guests by loyalty points
Show all platinum members
How many guests registered this month?
```

#### Booking Data
```
Show all confirmed bookings for tomorrow
What's the booking cancellation rate?
Which rooms are booked next week?
```

#### Hotel Performance
```
Show hotels with 4+ star rating
Which hotel has the most reviews?
List hotels in New York with available rooms
```

---

## 🔑 Key Files

| File | Purpose |
|------|---------|
| `.env` | Your API key (SECURE!) |
| `includes/openai_helper.php` | AI integration |
| `pages/admin/admin_ai_query.php` | Main UI |
| `db/09_ai_assistant.sql` | Database tables |

---

## ⚡ Quick Commands

### Test API Connection
```php
http://localhost/SmartStay/test_openai_connection.php
```

### Check .env File
```bash
cd d:\xampp\htdocs\SmartStay
type .env
```

### Restart Apache
```
XAMPP Control Panel → Apache → Restart
```

### View Database Tables
```sql
SHOW TABLES LIKE 'ai_%';
```

---

## 🛠️ Quick Fixes

| Problem | Solution |
|---------|----------|
| **"API Key not configured"** | Check `.env` file exists, restart Apache |
| **"cURL error"** | Enable `extension=curl` in php.ini |
| **"Table doesn't exist"** | Import `db/09_ai_assistant.sql` |
| **"Only SELECT allowed"** | This is correct! Security feature working |
| **Generation slow** | Check internet, ping api.openai.com |

---

## 💰 Cost Reference

| Usage | Monthly Cost |
|-------|--------------|
| 10 queries | ~$0.10 |
| 100 queries | ~$10-15 |
| 1000 queries | ~$100-150 |

**Model**: GPT-4 Turbo @ ~$0.01/query

---

## 🎯 Feature Checklist

- ✅ Natural Language to SQL
- ✅ Query History (auto-saved)
- ✅ Favorites (save & organize)
- ✅ Admin-Only Access
- ✅ Read-Only Security
- ✅ Usage Tracking
- ✅ AI Explanations
- ✅ Quick Examples

---

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| `AI_IMPLEMENTATION_SUMMARY.md` | Complete overview |
| `AI_QUERY_SETUP.md` | Installation guide |
| `AI_QUERY_README.md` | User manual |
| `INSTALLATION_CHECKLIST.md` | Step-by-step checklist |

---

## 🔒 Security Notes

- ⚠️ **NEVER** commit `.env` to Git
- ✅ Only SELECT queries allowed
- ✅ DROP/DELETE/UPDATE blocked
- ✅ SQL injection protection active
- ✅ Admin authentication required

---

## 🎓 Usage Tips

1. **Be Specific**: "Show top 10 hotels by revenue" > "Show hotels"
2. **Use Dates**: "Show bookings for December 2025"
3. **Include Metrics**: "Show hotels with average rating and total bookings"
4. **Save Favorites**: Reuse common queries
5. **Check History**: Don't regenerate the same query

---

## 📊 Table Structure

### ai_query_history
Stores all generated queries with execution status

### ai_query_favorites  
User-saved favorite queries with categories

### ai_usage_stats
Daily usage tracking and cost monitoring

---

## 🚨 Emergency Contacts

### OpenAI Support
- Status: https://status.openai.com
- Dashboard: https://platform.openai.com
- API Keys: https://platform.openai.com/api-keys

### Local Testing
- phpMyAdmin: http://localhost/phpmyadmin
- Connection Test: http://localhost/SmartStay/test_openai_connection.php

---

## ⏱️ Expected Timings

| Action | Time |
|--------|------|
| Query generation | 2-5 sec |
| Query execution | <1 sec |
| Page load | <2 sec |
| Complete workflow | <10 sec |

---

## 🎯 First-Time User Flow

1. **Login** as admin
2. **Click** "🤖 AI Query" in navigation
3. **Type** "Show all hotels"
4. **Click** "Generate SQL Query"
5. **Review** generated SQL
6. **Click** "Execute Query"
7. **View** results
8. **Save** to favorites (optional)

---

## 📱 Quick Access Buttons

In AI Query Assistant page:

| Button | Purpose |
|--------|---------|
| **Hotels in NY** | Example query |
| **Last Month Revenue** | Example query |
| **Top Guests** | Example query |
| **Upcoming Events** | Example query |
| **Available Rooms** | Example query |

---

## 🔧 Configuration Variables

### .env Settings
```env
OPENAI_API_KEY=sk-proj-...
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_MAX_TOKENS=2000
OPENAI_TEMPERATURE=0.1
```

### Customization
- Change model to `gpt-3.5-turbo` for cheaper queries
- Increase `MAX_TOKENS` for longer responses
- Adjust `TEMPERATURE` (0.0-1.0) for creativity

---

## ✅ Success Indicators

You're ready when you see:
- ✅ All tests pass in connection test
- ✅ Query generates in 2-5 seconds
- ✅ SQL displays with syntax highlighting
- ✅ Results show in table format
- ✅ History saves automatically
- ✅ Favorites can be saved

---

## 🎉 You're All Set!

**Everything you need to know in one place!**

### Next Steps:
1. Import database (`09_ai_assistant.sql`)
2. Test connection
3. Try first query
4. Save favorite
5. Explore features!

---

**Version**: 1.0.0  
**Date**: October 22, 2025  
**Status**: ✅ Production Ready

**Print this card for quick reference!** 📄
