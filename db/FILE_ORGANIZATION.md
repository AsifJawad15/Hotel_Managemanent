# SmartStay Database - File Organization Guide

## Current File Structure (January 15, 2025)

### ✅ MAIN FILES (Use These)

These are the **official modular database files** you should use:

| File | Purpose | Required | Size |
|------|---------|----------|------|
| **01_schema.sql** | Core tables and structure | ✅ Yes | ~850 lines |
| **02_procedures.sql** | Stored procedures | ✅ Yes | ~300 lines |
| **03_functions.sql** | Functions | ✅ Yes | ~150 lines |
| **04_triggers.sql** | Automated triggers | ✅ Yes | ~200 lines |
| **05_views.sql** | Report views | ✅ Yes | ~250 lines |
| **06_indexes.sql** | Performance indexes | ✅ Yes | ~100 lines |
| **07_sample_data.sql** | Test data | ⚠️ Optional | ~600 lines |

**Total Lines:** ~2,450 lines (organized and documented)

---

### 📚 DOCUMENTATION FILES (Read These)

| File | Purpose | Description |
|------|---------|-------------|
| **README.md** | Complete documentation | Full technical guide with examples |
| **INSTALLATION.md** | Setup guide | Step-by-step installation instructions |
| **database_structure.txt** | Quick reference | Table structures and relationships |
| **REORGANIZATION_SUMMARY.md** | Change log | What was done and why |
| **FILE_ORGANIZATION.md** | This file | File usage guide |

---

### 📦 LEGACY FILES (Archive or Delete)

These files are from previous versions and **can be safely archived or deleted**:

| File | Status | Notes |
|------|--------|-------|
| ~~smart_stay.sql~~ | 🔴 **REPLACED** | Original 2243-line dump - keep as backup |
| ~~enhanced_smart_stay.sql~~ | 🔴 **REPLACED** | Earlier enhanced version - superseded |
| ~~sample_data_inserts.sql~~ | 🔴 **REPLACED** | Old sample data - now in 07_sample_data.sql |
| ~~plsql_procedures.sql~~ | 🔴 **REPLACED** | Old procedures - now in 02_procedures.sql |
| ~~simple_price_procedure.sql~~ | 🔴 **REPLACED** | Single procedure - now in 02_procedures.sql |
| ~~advanced_queries.sql~~ | 🟡 **OPTIONAL** | Example queries - useful for reference |
| ~~test_price_procedure.sql~~ | 🟡 **OPTIONAL** | Test queries - useful for development |
| ~~cleanup_sample_data.sql~~ | 🟡 **OPTIONAL** | Cleanup script - keep if needed |

---

## Quick Start Guide

### For New Installation

Use only the **7 main SQL files** in order:

```bash
# Navigate to db folder
cd d:\xampp\htdocs\SmartStay\db

# Install in this exact order:
mysql -u root -p smart_stay < 01_schema.sql
mysql -u root -p smart_stay < 02_procedures.sql
mysql -u root -p smart_stay < 03_functions.sql
mysql -u root -p smart_stay < 04_triggers.sql
mysql -u root -p smart_stay < 05_views.sql
mysql -u root -p smart_stay < 06_indexes.sql
mysql -u root -p smart_stay < 07_sample_data.sql  # Optional
```

Or use **phpMyAdmin** and import files 01-07 in order.

---

## File Comparison

### What's Different?

#### Old Structure (smart_stay.sql - 2243 lines)
```
❌ Single monolithic file
❌ Hard to find specific components
❌ Must reload everything to update one procedure
❌ Difficult to understand structure
❌ Limited documentation
```

#### New Structure (01-07 files - 2450 lines)
```
✅ 7 modular files by functionality
✅ Easy to locate specific components
✅ Update individual files independently
✅ Clear organization and purpose
✅ Comprehensive documentation
✅ Enhanced with 7 views
✅ Performance indexes documented
✅ Installation guides included
```

---

## What to Do with Old Files

### Option 1: Archive (Recommended)
Create an archive folder:

```bash
# Create archive folder
mkdir d:\xampp\htdocs\SmartStay\db\archive

# Move old files
move smart_stay.sql archive\
move enhanced_smart_stay.sql archive\
move sample_data_inserts.sql archive\
move plsql_procedures.sql archive\
move simple_price_procedure.sql archive\
```

### Option 2: Delete (If Sure)
**⚠️ WARNING:** Only delete if you have backups!

```bash
del smart_stay.sql
del enhanced_smart_stay.sql
del sample_data_inserts.sql
del plsql_procedures.sql
del simple_price_procedure.sql
```

### Option 3: Keep (For Reference)
You can keep the old files for reference, but **always use the new 01-07 files** for actual installation.

---

## File Usage Matrix

| Task | Files to Use |
|------|--------------|
| **Fresh Database Install** | 01 → 02 → 03 → 04 → 05 → 06 → 07 |
| **Production Install** | 01 → 02 → 03 → 04 → 05 → 06 (skip 07) |
| **Update Procedures Only** | 02_procedures.sql |
| **Add New Views** | 05_views.sql |
| **Performance Tuning** | 06_indexes.sql |
| **Add Test Data** | 07_sample_data.sql |
| **Read Documentation** | README.md, INSTALLATION.md |
| **Quick Reference** | database_structure.txt |
| **Understand Changes** | REORGANIZATION_SUMMARY.md |

---

## Version Control

### Current Version: 2.0

**Changes from v1.0:**
- ✅ Modular file structure
- ✅ 7 business views added
- ✅ Enhanced stored procedures
- ✅ Full-text search indexes
- ✅ Comprehensive documentation
- ✅ Installation automation
- ✅ Performance optimizations

**Previous Version: 1.0**
- Files: smart_stay.sql, enhanced_smart_stay.sql
- Status: Superseded, archived

---

## Troubleshooting

### Problem: "Which file should I use?"
**Answer:** Use the numbered files (01-07) in order. Ignore other SQL files.

### Problem: "I have old database installed"
**Answer:** 
1. Backup: `mysqldump -u root -p smart_stay > backup.sql`
2. Drop: `DROP DATABASE smart_stay;`
3. Reinstall: Follow INSTALLATION.md with new files

### Problem: "Can I use both old and new files?"
**Answer:** No! Use ONLY the new numbered files (01-07). Old files are incompatible.

### Problem: "I need sample data from old files"
**Answer:** All sample data is now in **07_sample_data.sql** with improvements.

---

## Recommended Folder Structure

```
SmartStay/
└── db/
    ├── 01_schema.sql                    ← Use
    ├── 02_procedures.sql                ← Use
    ├── 03_functions.sql                 ← Use
    ├── 04_triggers.sql                  ← Use
    ├── 05_views.sql                     ← Use
    ├── 06_indexes.sql                   ← Use
    ├── 07_sample_data.sql               ← Use
    ├── README.md                        ← Read
    ├── INSTALLATION.md                  ← Read
    ├── database_structure.txt           ← Read
    ├── REORGANIZATION_SUMMARY.md        ← Read
    ├── FILE_ORGANIZATION.md             ← This file
    │
    ├── advanced_queries.sql             ← Optional: Keep for examples
    ├── test_price_procedure.sql         ← Optional: Keep for testing
    ├── cleanup_sample_data.sql          ← Optional: Keep for cleanup
    │
    └── archive/                         ← Move old files here
        ├── smart_stay.sql               (Original backup)
        ├── enhanced_smart_stay.sql      (Old version)
        ├── sample_data_inserts.sql      (Superseded)
        ├── plsql_procedures.sql         (Superseded)
        └── simple_price_procedure.sql   (Superseded)
```

---

## Migration Checklist

If migrating from old structure:

- [ ] Backup current database
- [ ] Export existing data (if needed)
- [ ] Read INSTALLATION.md
- [ ] Drop old database
- [ ] Install 01_schema.sql
- [ ] Install 02_procedures.sql
- [ ] Install 03_functions.sql
- [ ] Install 04_triggers.sql
- [ ] Install 05_views.sql
- [ ] Install 06_indexes.sql
- [ ] Install 07_sample_data.sql (or restore your data)
- [ ] Verify installation (see INSTALLATION.md)
- [ ] Test all features
- [ ] Archive old SQL files
- [ ] Update documentation references

---

## Key Takeaways

### ✅ DO
- Use numbered files 01-07 in order
- Read documentation files
- Follow INSTALLATION.md for setup
- Archive old files for safety
- Test after installation

### ❌ DON'T
- Mix old and new SQL files
- Skip files or change order
- Delete old files without backup
- Use enhanced_smart_stay.sql (outdated)
- Ignore installation documentation

---

## Support

**Questions about file organization?**
1. Read this file (FILE_ORGANIZATION.md)
2. Check INSTALLATION.md for setup
3. Review REORGANIZATION_SUMMARY.md for changes
4. Consult README.md for technical details
5. Check database_structure.txt for quick reference

**Need help with specific files?**
- Schema issues → 01_schema.sql
- Procedure problems → 02_procedures.sql
- Function errors → 03_functions.sql
- Trigger issues → 04_triggers.sql
- View problems → 05_views.sql
- Performance → 06_indexes.sql
- Sample data → 07_sample_data.sql

---

## Summary

**Use These 7 Files:**
1. 01_schema.sql
2. 02_procedures.sql
3. 03_functions.sql
4. 04_triggers.sql
5. 05_views.sql
6. 06_indexes.sql
7. 07_sample_data.sql

**Read These Docs:**
- README.md (comprehensive guide)
- INSTALLATION.md (setup steps)
- database_structure.txt (quick reference)

**Archive These:**
- smart_stay.sql
- enhanced_smart_stay.sql
- sample_data_inserts.sql
- plsql_procedures.sql
- simple_price_procedure.sql

---

**Last Updated:** January 15, 2025  
**Database Version:** 2.0  
**Status:** Production Ready ✅
