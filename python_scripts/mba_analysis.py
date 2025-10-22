import json
import sys
import os
from sqlalchemy import create_engine
import pandas as pd
from mlxtend.frequent_patterns import apriori, association_rules
from mlxtend.preprocessing import TransactionEncoder

# Add the current directory to Python path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

# CONNECT DB
username = "root"
password = "S34_dr4k3"
host = "127.0.0.1"
port = 3306
database = "techboxx"

# ---------- DB CONNECTION ----------
engine = create_engine(f"mysql+pymysql://{username}:{password}@{host}:{port}/{database}")

# ---------- BUILD LOOKUP MAPS ----------
def load_component_maps():
    """Load all component data into lookup maps"""
    maps = {}
    
    # CPU - Added stock column
    cpu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM cpus", engine)
    cpu_map = {}
    for row in cpu_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        cpu_map[row.id] = {
            'name': clean_name,
            'type': 'cpu',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'cpus',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['cpu'] = cpu_map

    # GPU - Added stock column
    gpu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM gpus", engine)
    gpu_map = {}
    for row in gpu_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        gpu_map[row.id] = {
            'name': clean_name,
            'type': 'gpu',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'gpus',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['gpu'] = gpu_map

    # RAM - Added stock column
    ram_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM rams", engine)
    ram_map = {}
    for row in ram_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        ram_map[row.id] = {
            'name': clean_name,
            'type': 'ram',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'rams',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['ram'] = ram_map

    # Motherboard - Added stock column
    mobo_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM motherboards", engine)
    mobo_map = {}
    for row in mobo_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        mobo_map[row.id] = {
            'name': clean_name,
            'type': 'motherboard',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'motherboards',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['motherboard'] = mobo_map

    # PSU - Added stock column
    psu_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM psus", engine)
    psu_map = {}
    for row in psu_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        psu_map[row.id] = {
            'name': clean_name,
            'type': 'psu',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'psus',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['psu'] = psu_map

    # Case - Added stock column
    case_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM pc_cases", engine)
    case_map = {}
    for row in case_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        case_map[row.id] = {
            'name': clean_name,
            'type': 'case',  # Changed from 'pc_case' to 'case'
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'pc_cases',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['case'] = case_map  # Changed from 'pc_case' to 'case'

    # Cooler - Added stock column
    cooler_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, stock FROM coolers", engine)
    cooler_map = {}
    for row in cooler_df.itertuples():
        clean_name = f"{row.brand} {row.model}"
        cooler_map[row.id] = {
            'name': clean_name,
            'type': 'cooler',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'coolers',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['cooler'] = cooler_map

    # Storage - Added stock column
    storage_df = pd.read_sql("SELECT id, brand, model, build_category_id, price, image, storage_type, stock FROM storages", engine)
    storage_map = {}
    for row in storage_df.itertuples():
        clean_name = f"{row.brand} {row.model} {row.storage_type}"
        storage_map[row.id] = {
            'name': clean_name,
            'type': 'storage',
            'price': float(row.price),
            'category': int(row.build_category_id),
            'image': row.image if pd.notna(row.image) else 'images/placeholder.png',
            'table': 'storages',
            'stock': int(row.stock) if pd.notna(row.stock) else 0
        }
    maps['storage'] = storage_map

    return maps

def get_product_recommendations(product_name, comp_type):
    """Get MBA recommendations for a specific product"""
    
    # Load component maps
    lookup_maps = load_component_maps()
    
    # Load user builds
    query = "SELECT * FROM user_builds"
    df = pd.read_sql(query, engine)
    
    # Prepare transactions with component names
    transactions = []
    component_details = {}  # Store component details by name
    
    for _, row in df.iterrows():
        items = []
        for col in ["pc_case_id", "motherboard_id", "cpu_id", "gpu_id", "storage_id", "ram_id", "psu_id", "cooler_id"]:
            if pd.notna(row[col]):
                comp_type_col = col.replace("_id", "")
                # Map 'pc_case' to 'case' for lookup
                lookup_type = 'case' if comp_type_col == 'pc_case' else comp_type_col
                comp_id = int(row[col])
                
                if lookup_type in lookup_maps and comp_id in lookup_maps[lookup_type]:
                    comp_data = lookup_maps[lookup_type][comp_id]
                    comp_name = comp_data['name']
                    
                    # Store component details
                    component_details[comp_name] = {
                        'name': comp_name,
                        'type': comp_data['type'],
                        'price': comp_data['price'],
                        'id': comp_id,
                        'image': comp_data['image'],
                        'table': comp_data['table'],
                        'stock': comp_data['stock']
                    }
                    
                    items.append(comp_name)
        
        if items:  # Only add non-empty transactions
            transactions.append(items)
    
    if len(transactions) < 10:
        return []
    
    # Run MBA analysis
    try:
        te = TransactionEncoder()
        te_ary = te.fit(transactions).transform(transactions)
        basket = pd.DataFrame(te_ary, columns=te.columns_)
        
        # Adjust min_support based on data size
        min_support = max(0.05, 3.0 / len(transactions))
        
        frequent_itemsets = apriori(basket, min_support=min_support, use_colnames=True, verbose=0)
        rules = association_rules(frequent_itemsets, metric="confidence", min_threshold=0.3)
        
        # Find recommendations
        recommendations = []
        target_product = product_name
        
        for _, rule in rules.iterrows():
            antecedents = list(rule['antecedents'])
            consequents = list(rule['consequents'])
            
            # Check if target product is in antecedents (if someone buys this, what else do they buy?)
            if target_product in antecedents:
                for consequent in consequents:
                    if consequent != target_product and consequent in component_details:
                        rec_data = component_details[consequent].copy()
                        rec_data['confidence'] = float(rule['confidence'])
                        rec_data['support'] = float(rule['support'])
                        recommendations.append(rec_data)
            
            # Also check if target is in consequents (what products lead to buying this?)
            elif target_product in consequents:
                for antecedent in antecedents:
                    if antecedent != target_product and antecedent in component_details:
                        rec_data = component_details[antecedent].copy()
                        rec_data['confidence'] = float(rule['confidence'])
                        rec_data['support'] = float(rule['support'])
                        recommendations.append(rec_data)
        
        # Remove duplicates and sort by confidence
        unique_recommendations = {}
        for rec in recommendations:
            if rec['name'] not in unique_recommendations:
                unique_recommendations[rec['name']] = rec
            elif rec['confidence'] > unique_recommendations[rec['name']]['confidence']:
                unique_recommendations[rec['name']] = rec
        
        # Convert back to list and sort by confidence
        final_recommendations = sorted(unique_recommendations.values(), 
                                     key=lambda x: x['confidence'], 
                                     reverse=True)[:3]
        
        return final_recommendations
        
    except Exception as e:
        return []

# ---------- MAIN EXECUTION ----------
if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] == "recommend":
        product_name = sys.argv[2] if len(sys.argv) > 2 else ""
        comp_type = sys.argv[3] if len(sys.argv) > 3 else ""
        
        if product_name:
            try:
                recommendations = get_product_recommendations(product_name, comp_type)
                result = {
                    "status": "success",
                    "product": product_name,
                    "recommendations": recommendations
                }
                # ONLY OUTPUT JSON - NO OTHER PRINT STATEMENTS!
                print(json.dumps(result))
            except Exception as e:
                error_result = {
                    "status": "error",
                    "message": f"Analysis failed: {str(e)}"
                }
                print(json.dumps(error_result))
        else:
            print(json.dumps({"status": "error", "message": "No product name provided"}))
    else:
        # Run full analysis mode
        print(json.dumps({"status": "error", "message": "Use 'recommend' mode for product recommendations"}))