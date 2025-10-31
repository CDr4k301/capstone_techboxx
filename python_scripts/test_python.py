import json
import sys
from sqlalchemy import create_engine
import pandas as pd
from mlxtend.frequent_patterns import apriori, association_rules
from mlxtend.preprocessing import TransactionEncoder

# CONNECT DB
username = "root"
password = "S34_dr4k3"
host = "127.0.0.1"
port = 3306
database = "techboxx"

# ---------- DB CONNECTION ----------
engine = create_engine(f"mysql+pymysql://{username}:{password}@{host}:{port}/{database}")

# ---------- CLEAN LOOKUP MAPS STRUCTURE ----------
class ComponentLookup:
    def __init__(self):
        self.components = {}
        self.category_mapping = {}
        self.brand_mapping = {}
        self.price_mapping = {}
        self.id_mapping = {}
        self.detailed_maps = {}
        
    def load_component_data(self, table_name, component_type):
        """Load component data from database and create structured lookup maps"""
        df = pd.read_sql(f"SELECT * FROM {table_name}", engine)
        
        component_map = {}
        for row in df.itertuples():
            # Store detailed component information
            component_info = {
                'id': row.id,
                'brand': row.brand,
                'model': row.model,
                'build_category_id': row.build_category_id,
                'price': float(row.price),
                'full_name': f"{row.brand} {row.model}",
                'component_type': component_type
            }
            
            # Add storage_type for storage components
            if hasattr(row, 'storage_type'):
                component_info['storage_type'] = row.storage_type.lower()
                component_info['full_name'] = f"{row.brand} {row.model} {row.storage_type}"
            
            component_map[row.id] = component_info
            
            # Create lookup keys
            component_key = f"{component_type}_id:{row.id}"
            
            # Populate lookup maps
            self.category_mapping[component_key] = row.build_category_id
            self.brand_mapping[component_key] = row.brand
            self.price_mapping[component_key] = float(row.price)
            self.id_mapping[component_key] = row.id
            self.components[component_key] = component_info
        
        self.detailed_maps[component_type] = component_map
        return component_map

# Initialize lookup system
lookup_system = ComponentLookup()

# Load all component types
cpu_map = lookup_system.load_component_data("cpus", "cpu")
gpu_map = lookup_system.load_component_data("gpus", "gpu")
ram_map = lookup_system.load_component_data("rams", "ram")
mobo_map = lookup_system.load_component_data("motherboards", "motherboard")
psu_map = lookup_system.load_component_data("psus", "psu")
case_map = lookup_system.load_component_data("pc_cases", "pc_case")
cooler_map = lookup_system.load_component_data("coolers", "cooler")
storage_map = lookup_system.load_component_data("storages", "storage")

# Easy access to all lookup maps
lookup_maps = lookup_system.detailed_maps
component_to_category = lookup_system.category_mapping
component_to_brand = lookup_system.brand_mapping
component_to_price = lookup_system.price_mapping
component_to_id = lookup_system.id_mapping

# ---------- HELPER FUNCTIONS FOR EASY DATA FETCHING ----------
def get_component_info(component_type, component_id):
    """Get complete component information by type and ID"""
    if component_type in lookup_maps and component_id in lookup_maps[component_type]:
        return lookup_maps[component_type][component_id]
    return None

def get_component_by_key(component_key):
    """Get component information by full key (e.g., 'cpu_id:123')"""
    return lookup_system.components.get(component_key)

def get_components_by_category(component_type, category_id):
    """Get all components of a specific type and category"""
    if component_type not in lookup_maps:
        return []
    
    return [comp for comp in lookup_maps[component_type].values() 
            if comp['build_category_id'] == category_id]

def get_components_by_brand(component_type, brand):
    """Get all components of a specific type and brand"""
    if component_type not in lookup_maps:
        return []
    
    return [comp for comp in lookup_maps[component_type].values() 
            if comp['brand'].lower() == brand.lower()]

def find_cheaper_alternatives(component_type, category_id, max_price, brand_filter=None):
    """Find cheaper alternatives for a component"""
    alternatives = get_components_by_category(component_type, category_id)
    
    filtered_alternatives = []
    for comp in alternatives:
        if comp['price'] < max_price:
            if brand_filter and comp['brand'].lower() != brand_filter.lower():
                continue
            filtered_alternatives.append(comp)
    
    return sorted(filtered_alternatives, key=lambda x: x['price'], reverse=True)

def find_component_id_by_name(component_type, component_name):
    """Find component ID by name matching"""
    if component_type not in lookup_maps:
        return None
    
    for comp_id, comp_info in lookup_maps[component_type].items():
        if comp_info['full_name'].strip().lower() == component_name.strip().lower():
            return comp_id
    return None

# ---------- CONFIGURATION ----------
category_mapping = {
    "general use": 1,
    "gaming": 2,
    "graphics intensive": 3
}

cpu_brands = ["AMD", "Intel"]

# Parse arguments
if len(sys.argv) >= 2:
    category_input = sys.argv[1].lower()
    best_category = category_mapping.get(category_input)
else:
    best_category = None

preferred_cpu_brand = None
if len(sys.argv) >= 3:
    cpu_input = sys.argv[2].lower()
    if cpu_input == "amd":
        preferred_cpu_brand = "AMD"
    elif cpu_input == "intel":
        preferred_cpu_brand = "Intel"
    else:
        preferred_cpu_brand = None

# Parse budget from command line (4th argument)
user_budget = None
if len(sys.argv) >= 4:
    try:
        user_budget = float(sys.argv[3])
    except ValueError:
        user_budget = None

# ---------- LOAD USER BUILDS ----------
query = "SELECT * FROM user_builds"
df = pd.read_sql(query, engine)

transactions = []
for _, row in df.iterrows():
    items = []
    for col in ["pc_case_id", "motherboard_id", "cpu_id", "gpu_id", "storage_id", "ram_id", "psu_id", "cooler_id"]:
        if pd.notna(row[col]):
            items.append(f"{col.replace('_id', '')}_id:{row[col]}")
    transactions.append(items)

# ---------- APRIORI ----------
te = TransactionEncoder()
te_ary = te.fit(transactions).transform(transactions)
basket = pd.DataFrame(te_ary, columns=te.columns_)

frequent_itemsets = apriori(basket, min_support=0.01, use_colnames=True)
rules = association_rules(frequent_itemsets, metric="confidence", min_threshold=0.6)

# ---------- RECOMMENDATION FUNCTIONS (UPDATED TO USE NEW LOOKUP SYSTEM) ----------
def get_best_recommendation_by_category():
    component_types = ["pc_case", "motherboard", "cpu", "gpu", "storage", "ram", "psu", "cooler"]
    
    # Score each build category based on frequent itemsets
    category_scores = {}
    
    for _, itemset_row in frequent_itemsets.iterrows():
        itemset = itemset_row['itemsets']
        support = itemset_row['support']
        
        # Get categories for items in this itemset
        categories_in_set = []
        for item in itemset:
            if item in component_to_category:
                categories_in_set.append(component_to_category[item])
        
        if categories_in_set:
            unique_categories = set(categories_in_set)
            for category in unique_categories:
                if category not in category_scores:
                    category_scores[category] = 0
                category_purity = categories_in_set.count(category) / len(categories_in_set)
                category_scores[category] += support * category_purity
    
    # Find the best category
    if not category_scores:
        return get_fallback_recommendations()
    
    selected_category = best_category if best_category else max(category_scores, key=category_scores.get)
    
    # Get the best component for each type within this category
    recommendations = {}
    
    for comp_type in component_types:
        col = f"{comp_type}_id"
        category_items = []
        
        for _, itemset_row in frequent_itemsets.iterrows():
            itemset = itemset_row['itemsets']
            support = itemset_row['support']
            
            for item in itemset:
                if item.startswith(comp_type) and item in component_to_category:
                    if component_to_category[item] == selected_category:
                        if comp_type == "cpu" and preferred_cpu_brand:
                            item_brand = component_to_brand[item]
                            if item_brand.lower() != preferred_cpu_brand.lower():
                                continue
                        category_items.append((item, support))
        
        if category_items:
            best_item = max(category_items, key=lambda x: x[1])[0]
            comp_type_str, comp_id = best_item.split(":")
            comp_id = int(comp_id)

            comp_info = get_component_info(comp_type, comp_id)
            if comp_info:
                recommendation_data = {
                    "id": comp_id,
                    "name": comp_info['full_name'],
                    "price": comp_info['price']
                }
                
                if comp_type == "storage":
                    recommendation_data["type"] = comp_info.get('storage_type', 'unknown')
                    recommendations["storage"] = recommendation_data
                else:
                    recommendations[comp_type] = recommendation_data
        else:
            fallback_item = get_fallback_for_component(comp_type, selected_category)
            recommendations[comp_type] = {
                "id": None,
                "name": fallback_item,
                "price": 0
            }
    
    return recommendations

def get_recommendations_for_category(target_category, preferred_cpu_brand=None):
    recommendations = {}
    component_types = ["pc_case", "motherboard", "cpu", "gpu", "storage", "ram", "psu", "cooler"]
    
    for comp_type in component_types:
        col = f"{comp_type}_id"
        category_items = []
        
        for _, itemset_row in frequent_itemsets.iterrows():
            itemset = itemset_row['itemsets']
            support = itemset_row['support']
            
            for item in itemset:
                if item.startswith(comp_type) and item in component_to_category:
                    if component_to_category[item] == target_category:
                        if comp_type == "cpu" and preferred_cpu_brand:
                            item_brand = component_to_brand[item]
                            if item_brand.lower() != preferred_cpu_brand.lower():
                                continue
                        category_items.append((item, support))
        
        if category_items:
            best_item = max(category_items, key=lambda x: x[1])[0]
            comp_type_str, comp_id = best_item.split(":")
            comp_id = int(comp_id)
            
            comp_info = get_component_info(comp_type, comp_id)
            if comp_info:
                recommendation_data = {
                    "id": comp_id,
                    "name": comp_info['full_name'],
                    "price": comp_info['price']
                }
                
                if comp_type == "storage":
                    recommendation_data["type"] = comp_info.get('storage_type', 'unknown')
                    recommendations["storage"] = recommendation_data
                else:
                    recommendations[comp_type] = recommendation_data
        else:
            fallback_item = get_fallback_for_component(comp_type, target_category)
            recommendations[comp_type] = {
                "id": None,
                "name": fallback_item, 
                "price": 0
            }
    
    return recommendations

def get_budget_recommendations(user_budget, target_category=None, preferred_cpu_brand=None, budget_tolerance=0.05):
    if target_category:
        initial_recommendations = get_recommendations_for_category(target_category, preferred_cpu_brand)
    else:
        initial_recommendations = get_best_recommendation_by_category()
    
    component_details = {}
    total_price = 0
    component_types = ["pc_case", "motherboard", "cpu", "gpu", "storage", "ram", "psu", "cooler"]
    
    # Calculate initial total price
    for comp_type in component_types:
        if comp_type in initial_recommendations and initial_recommendations[comp_type]:
            rec = initial_recommendations[comp_type]
            if rec["id"]:
                comp_info = get_component_info(comp_type, rec["id"])
                if comp_info:
                    component_details[comp_type] = {
                        "name": comp_info['full_name'],
                        "id": rec["id"],
                        "price": comp_info['price'],
                        "category": comp_info['build_category_id']
                    }
                    total_price += comp_info['price']
    
    # Optimize for budget if over
    if total_price > user_budget:
        sorted_components = sorted(component_details.items(), key=lambda x: x[1]["price"], reverse=True)
        for comp_type, details in sorted_components:
            if total_price <= user_budget * (1 + budget_tolerance):
                break
            
            cheaper_alternatives = find_cheaper_alternatives(
                comp_type, details["category"], details["price"],
                preferred_cpu_brand if comp_type == "cpu" else None
            )
            
            if cheaper_alternatives:
                cheaper_alt = cheaper_alternatives[0]  # Get the most expensive cheaper option
                savings = details["price"] - cheaper_alt['price']
                if savings > 0:
                    component_details[comp_type] = {
                        "name": cheaper_alt['full_name'],
                        "id": cheaper_alt['id'],
                        "price": cheaper_alt['price'],
                        "category": cheaper_alt['build_category_id']
                    }
                    total_price -= savings
    
    # Format final recommendations
    final_recommendations = {}
    final_total = 0
    
    for comp_type in component_types:
        if comp_type in component_details:
            detail = component_details[comp_type]
            comp_info = get_component_info(comp_type, detail["id"])
            
            if comp_type == "storage":
                final_recommendations["storage"] = {
                    "id": detail["id"],
                    "name": detail["name"],
                    "price": detail["price"],
                    "type": comp_info.get('storage_type', 'unknown') if comp_info else 'unknown'
                }
            else:
                final_recommendations[comp_type] = {
                    "id": detail["id"],
                    "name": detail["name"], 
                    "price": detail["price"]
                }
            final_total += detail["price"]
        else:
            final_recommendations[comp_type] = {
                "id": None,
                "name": None, 
                "price": 0
            }
    
    final_recommendations["budget_summary"] = {
        "user_budget": user_budget,
        "total_price": final_total,
        "remaining_budget": user_budget - final_total,
        "within_budget": final_total <= user_budget * (1 + budget_tolerance)
    }
    
    return final_recommendations

def get_fallback_for_component(comp_type, target_category):
    """Get any component of the given type from the target category"""
    components = get_components_by_category(comp_type, target_category)
    if components:
        return components[0]['full_name']
    return None

def get_fallback_recommendations():
    recommendations = {}
    component_types = ["pc_case", "motherboard", "cpu", "gpu", "storage", "ram", "psu", "cooler"]
    
    for comp_type in component_types:
        col = f"{comp_type}_id"
        filtered = frequent_itemsets[frequent_itemsets['itemsets'].astype(str).str.contains(comp_type)]
        
        if not filtered.empty:
            top_item = filtered.sort_values("support", ascending=False).iloc[0]
            item = list(top_item['itemsets'])[0]
            comp_type_str, comp_id = item.split(":")
            comp_id = int(comp_id)
            
            comp_info = get_component_info(comp_type, comp_id)
            if comp_info:
                recommendation_data = {
                    "id": comp_id,
                    "name": comp_info['full_name'],
                    "price": comp_info['price']
                }
                
                if comp_type == "storage":
                    recommendation_data["type"] = comp_info.get('storage_type', 'unknown')
                    recommendations["storage"] = recommendation_data
                else:
                    recommendations[comp_type] = recommendation_data
        else:
            recommendations[comp_type] = {"id": None, "name": None, "price": 0}
    
    return recommendations

# ---------- GET RECOMMENDATIONS ----------
if user_budget:
    if best_category and preferred_cpu_brand:
        recommendations = get_budget_recommendations(user_budget, best_category, preferred_cpu_brand)
    elif best_category:
        recommendations = get_budget_recommendations(user_budget, best_category)
    else:
        recommendations = get_budget_recommendations(user_budget)
else:
    if best_category and preferred_cpu_brand:
        recommendations = get_recommendations_for_category(best_category, preferred_cpu_brand)
    elif best_category:
        recommendations = get_recommendations_for_category(best_category)
    else:
        recommendations = get_best_recommendation_by_category()

# ---------- OUTPUT JSON ----------
print(json.dumps(recommendations, indent=4, ensure_ascii=False))