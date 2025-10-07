// 3D JS
import * as THREE from 'https://esm.sh/three@0.155.0';
import { OrbitControls } from 'https://esm.sh/three@0.155.0/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'https://esm.sh/three@0.155.0/examples/jsm/loaders/GLTFLoader.js';
import interact from 'https://esm.sh/interactjs@1.10.17';

let scene, camera, renderer, controls;
let caseModel = null;
let moboModel = null;
let moboSlotPosition = null;
let selectedCaseModelUrl = null;
let selectedMoboModelUrl = null;
let caseMarker = null;
let moboMarker = null;
let draggingId = null;
let draggingEl = null;

init();
setupCatalogClickHandlers();
animate();

function init() {
    // Scene
    scene = new THREE.Scene();
    scene.background = null; // transparent background

    // Camera
    const container = document.getElementById('canvas-container');
    const width = container.clientWidth;
    const height = container.clientHeight;

    camera = new THREE.PerspectiveCamera(30, width / height, 0.1, 1000);
    camera.position.set(20, 0, 0);

    // Renderer
    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(width, height);
    container.appendChild(renderer.domElement);

    // Controls
    controls = new OrbitControls(camera, renderer.domElement);

    // Lights
    scene.add(new THREE.AmbientLight(0xffffff, 0.5));
    const dirLight = new THREE.DirectionalLight(0xffffff, 7.0);
    dirLight.position.set(2, 10, 5);
    scene.add(dirLight);

    // Resize listener
    window.addEventListener('resize', () => {
        const width = container.clientWidth;
        const height = container.clientHeight;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    });
}

function animate() {
    requestAnimationFrame(animate);
    renderer.render(scene, camera);
}

async function loadGLTFModel(url) {
    const loader = new GLTFLoader();
    const gltf = await loader.loadAsync(url);
    return gltf.scene;
}

// Catalog click (optional: preselect URLs)
function setupCatalogClickHandlers() {
  document.querySelectorAll('.build-catalog, .component-button').forEach(item => {
    item.addEventListener('click', () => {
      const modelUrl = item.getAttribute('data-model');
      const type = item.getAttribute('data-type');

      if (!modelUrl) {
        console.log('Model not available for this component.');
        return;
      }
      if (type === 'case') {
        selectedCaseModelUrl = modelUrl;
        console.log('Selected case URL:', selectedCaseModelUrl);
      } else if (type === 'motherboard') {
        selectedMoboModelUrl = modelUrl;
        console.log('Selected GPU URL:', selectedMoboModelUrl);
      }
    });
  });
}

// Interact.js drag/drop
interact('.component-button').draggable({
    listeners: {
        start(event) {
            draggingId = event.target.getAttribute('data-type');
            draggingEl = event.target;
            draggingEl.style.opacity = '0.5';
            document.body.style.cursor = 'grabbing';

            if (draggingId === 'case' && !caseModel) {
                caseMarker = new THREE.Mesh(
                    new THREE.BoxGeometry(4, 4, 4),
                    new THREE.MeshStandardMaterial({
                    color: 0x0000ff,
                    emissive: 0x000066,
                    transparent: true,
                    opacity: 0.3,
                    })
                );
                
                caseMarker.position.set(0, 0, 0);
                scene.add(caseMarker);
            }

            if (draggingId === 'motherboard' && caseModel) {
                const moboSlot = caseModel.getObjectByName('Slot_Mobo');
                if (moboSlot) {
                    moboMarker = new THREE.Mesh(
                    new THREE.BoxGeometry(3, 3, 0.1),
                    new THREE.MeshStandardMaterial({
                        color: 0x00ff00,
                        emissive: 0x003300,
                        transparent: true,
                        opacity: 0.4,
                    })
                    );

                    
                }
                moboMarker.rotation.x = 0; // No rotation on the Y axis
                moboMarker.rotation.y = Math.PI / 2;  // 90 degrees        
                moboMarker.rotation.z = 0;          // No rotation on the Z axis
                moboMarker.position.set(moboSlotPosition.x, moboSlotPosition.y + -1.4, moboSlotPosition.z + -2); // Position the moboMarker
                scene.add(moboMarker);


            }
        },
        move(event) {
        // Optional: visual feedback while dragging
        },
        async end(event) {
            draggingEl.style.opacity = '1';
            document.body.style.cursor = 'grab';

            const dropPos = getCanvasDropPosition(event.clientX, event.clientY);

            if (dropPos && draggingId === 'case' && !caseModel) {
                if (selectedCaseModelUrl) {
                await spawnCase(dropPos, selectedCaseModelUrl);
                }
            }
            if (dropPos && draggingId === 'motherboard' && caseModel) {
                await spawnGPUAtSlot();
            }

            if (caseMarker) {
                scene.remove(caseMarker);
                caseMarker = null;
            }
            if (moboMarker) {
                scene.remove(moboMarker);
                moboMarker = null;
            }

            draggingId = null;
            draggingEl = null;
        }
    }
});

function getCanvasDropPosition(clientX, clientY) {
    const rect = renderer.domElement.getBoundingClientRect();
    if (
        clientX < rect.left || clientX > rect.right ||
        clientY < rect.top || clientY > rect.bottom
    ) {
        return null;
    }
    const x = ((clientX - rect.left) / rect.width) * 2 - 1;
    const y = -((clientY - rect.top) / rect.height) * 2 + 1;

    const mouseVector = new THREE.Vector2(x, y);
    const raycaster = new THREE.Raycaster();
    raycaster.setFromCamera(mouseVector, camera);

    const planeZ = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0);
    const intersectionPoint = new THREE.Vector3();
    raycaster.ray.intersectPlane(planeZ, intersectionPoint);
    return intersectionPoint;
}

async function spawnCase(position, modelUrl) {
    if (caseModel) return;
    try {
        const model = await loadGLTFModel(modelUrl);
        model.position.copy(position);
        model.scale.setScalar(1.5);
        scene.add(model);
        caseModel = model;

        // Focus controls on the case
        controls.target.copy(model.position);
        controls.update();

        const gpuSlot = model.getObjectByName('Slot_Mobo');
        if (gpuSlot) {
        moboSlotPosition = new THREE.Vector3();
        gpuSlot.getWorldPosition(moboSlotPosition);
        console.log('GPU slot position:', moboSlotPosition);
        } else {
        moboSlotPosition = new THREE.Vector3(0, 0, 0);
        console.warn('GPU slot not found in case model');
        }
    } catch (err) {
        console.error('Failed to load case model', err);
    }
}

async function spawnGPUAtSlot() {
    if (!moboSlotPosition) {
        alert('GPU slot position unknown');
        return;
    }
    if (!selectedMoboModelUrl) {
        alert('Please select a GPU model first.');
        return;
    }
    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedMoboModelUrl);
        model.position.copy(moboSlotPosition);
        model.scale.setScalar(1.5);
        scene.add(model);
        moboModel = model;
    } catch (err) {
        console.error('Failed to load GPU model', err);
    }
}

function reloadScene() {
    // Remove case model
    if (caseModel) {
        scene.remove(caseModel);
        caseModel = null;
    }

    // Remove motherboard model
    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }

    // Remove CPU model
    if (cpuModel) {
        scene.remove(cpuModel);
        cpuModel = null;
    }

    // Reset the camera controls target to the origin (or wherever you prefer)
    controls.target.set(0, 0, 0);
    controls.update();

    // Log to confirm scene is reset
    console.log('Scene has been reloaded.');
}


// LAYOUT JS
document.addEventListener('DOMContentLoaded', () => {
    const arrow = document.querySelector('.component-arrow');
    const wrapper = document.querySelector('.catalog-wrapper');
    const componentButtons  = document.querySelectorAll('.component-section .component-button');
    const catalogItems = document.querySelectorAll('#catalogSection .build-catalog');

    arrow.addEventListener('click', () => {
        wrapper.classList.toggle('open');
        arrow.classList.toggle('rotated');
    });

    // FILTER BY TYPE
    componentButtons .forEach(button => {
        button.addEventListener('click', () => {
            const isActive = button.classList.contains('component-active');
            const selectedType = button.getAttribute('data-type');
            
            componentButtons .forEach(c => c.classList.remove('component-active'));
            
            if (isActive) {
                catalogItems.forEach(item => {
                    item.style.display = '';
                });
            } 
            else {
                button.classList.add('component-active');

                catalogItems.forEach(item => {
                    const itemType = item.getAttribute('data-type');
                    item.style.display = (itemType === selectedType) ? '' : 'none';                    
                });    
            }
            
        });
    });

    // SET IMAGE ON CLICK
    catalogItems.forEach(item => {
        item.addEventListener('click', () => {
            const itemType = item.getAttribute('data-type');
            const imageUrl = item.getAttribute('data-image');
            const selectedId = item.getAttribute('data-id');

            // FIND MATCHING COMPONENT BUTTON
            const targetButton = document.querySelector(`.component-button[data-type="${itemType}"]`);
            if (targetButton) {
                const imgTag = targetButton.querySelector('img');
                imgTag.src = imageUrl;
                imgTag.style.display = 'block';

                // Set the selected ID on the button to indicate the item has been selected
                targetButton.setAttribute('data-selected-id', selectedId);
            }
        });
    });

    // VALIDATION
document.getElementById('validateBuild').addEventListener('click', () => {
    const selections = {};
    document.querySelectorAll('.component-button').forEach(button => {
        const type = button.getAttribute('data-type');
        const selectedId = button.getAttribute('data-selected-id');
        if (selectedId) {
            selections[type + "_id"] = selectedId;
        }
    });

    // ✅ Condition 1: No components selected
    if (Object.keys(selections).length === 0) {
        alert("⚠️ No components selected.\nPlease choose at least one component before validating.");
        return;
    }

    // SEND TO BACKEND
    fetch('/techboxx/build/validate', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(selections)
    })
    .then(res => res.json())
    .then(data => {
        let message = '';

        if (data.errors && data.errors.length > 0) {
            message += '❌ Compatibility Errors:\n' + data.errors.join("\n") + '\n\n';
        }

        if (data.warnings && data.warnings.length > 0) {
            message += '⚠️ Warnings:\n' + data.warnings.join("\n") + '\n\n';
        }

        // ✅ Condition 2: No errors, no warnings, but components were selected
        if (
            (!data.errors || data.errors.length === 0) &&
            (!data.warnings || data.warnings.length === 0)
        ) {
            message = "✅ No issues found. However, make sure all components are added for a complete compatibility check.";
        }

        alert(message);
    })
    .catch(err => {
        console.error('Validation failed:', err);
        alert('❌ An error occurred while validating the build.');
    });
});
    document.getElementById('reloadButton').addEventListener('click', function() {
        reloadScene();
    });
});