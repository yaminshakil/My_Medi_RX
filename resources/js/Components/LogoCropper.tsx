import { Button } from '@/Components/ui/button';
import { getCroppedImg } from '@/lib/imageCropper';
import { Crop, Edit, RotateCw, Upload, X, ZoomIn, ZoomOut } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import Cropper from 'react-easy-crop';

const LogoCropper = ({ setData, field, initialImage = null, initialCropData = null }) => {
    const [image, setImage] = useState(null);
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [rotation, setRotation] = useState(0);
    const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);
    const [croppedImage, setCroppedImage] = useState(null);
    const [isCropping, setIsCropping] = useState(false);
    const fileInputRef = useRef(null);

    // Square aspect ratio for logos (1:1)
    const aspectRatio = 1;

    // Initialize with existing image if provided (for edit mode)
    useEffect(() => {
        if (initialImage) {
            setCroppedImage(initialImage);

            if (initialCropData) {
                try {
                    const cropData = typeof initialCropData === 'string' ? JSON.parse(initialCropData) : initialCropData;

                    if (cropData.rotation) {
                        setRotation(cropData.rotation);
                    }
                } catch (e) {
                    console.error('Error parsing crop data:', e);
                }
            }
        }
    }, [initialImage, initialCropData]);

    const onFileChange = async (e) => {
        if (e.target.files && e.target.files.length > 0) {
            const file = e.target.files[0];
            const imageDataUrl = await readFile(file);
            setImage(imageDataUrl);
            setIsCropping(true);
        }
    };

    const readFile = (file) => {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.addEventListener('load', () => resolve(reader.result));
            reader.readAsDataURL(file);
        });
    };

    const onCropChange = (crop) => {
        setCrop(crop);
    };

    const onCropComplete = useCallback((croppedArea, croppedAreaPixels) => {
        setCroppedAreaPixels(croppedAreaPixels);
    }, []);

    const handleCropComplete = useCallback(async () => {
        if (!image || !croppedAreaPixels) {
            console.error('No image or crop data available');
            return;
        }

        try {
            const croppedImageBlob = await getCroppedImg(image, croppedAreaPixels, rotation);

            // Create a file from the blob
            const file = new File([croppedImageBlob], 'logo.png', {
                type: 'image/png',
                lastModified: Date.now(),
            });

            // Create preview URL
            const previewUrl = URL.createObjectURL(croppedImageBlob);
            setCroppedImage(previewUrl);

            // Update form data
            setData((prev) => ({
                ...prev,
                [field]: file, // File object
                logo_crop_data: JSON.stringify({
                    ...croppedAreaPixels,
                    rotation: rotation,
                }),
            }));

            // Reset cropping state but keep editing mode
            setIsCropping(false);
            setImage(null);
        } catch (error) {
            console.error('Error cropping image:', error);
        }
    }, [image, croppedAreaPixels, rotation, setData]);

    const resetCropper = () => {
        setImage(null);
        setCrop({ x: 0, y: 0 });
        setZoom(1);
        setRotation(0);
        setCroppedAreaPixels(null);
        setIsCropping(false);

        if (initialImage) {
            setCroppedImage(initialImage);
            setData(field, null);
            setData('logo_crop_data', initialCropData || '');
        } else {
            setCroppedImage(null);
            setData(field, null);
            setData('logo_crop_data', '');
        }

        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const removeLogo = () => {
        setCroppedImage(null);
        setData(field, null);
        setData('logo_crop_data', '');
    };

    const startEditing = () => {
        if (croppedImage && croppedImage.startsWith('blob:')) {
            setImage(croppedImage);
        } else if (croppedImage) {
            fetch(croppedImage)
                .then((response) => response.blob())
                .then((blob) => {
                    const url = URL.createObjectURL(blob);
                    setImage(url);
                })
                .catch((error) => {
                    console.error('Error loading image for editing:', error);
                });
        }
        setIsCropping(true);
    };

    return (
        <div className="w-full">
            {croppedImage && !isCropping ? (
                <div className="mt-2 flex flex-col items-start">
                    <div className="h-auto w-full overflow-hidden rounded-lg border">
                        <img src={croppedImage} alt="Logo preview" className="h-full w-full object-contain" />
                    </div>
                    <div className="mt-2 flex space-x-2">
                        <Button type="button" onClick={startEditing} className="flex items-center rounded-md text-sm text-white">
                            <Edit className="mr-1 h-4 w-4" />
                            Edit
                        </Button>
                        <Button variant="destructive" type="button" onClick={removeLogo} className="flex cursor-pointer items-center text-white">
                            <X className="mr-1 h-4 w-4" />
                            Remove
                        </Button>
                    </div>
                </div>
            ) : isCropping ? (
                <div className="mt-2 space-y-4">
                    <div className="relative mx-auto h-64 w-64 overflow-hidden rounded-lg bg-gray-100">
                        <Cropper
                            image={image}
                            crop={crop}
                            zoom={zoom}
                            rotation={rotation}
                            aspect={aspectRatio}
                            onCropChange={onCropChange}
                            onCropComplete={onCropComplete}
                            onZoomChange={setZoom}
                            cropShape="rect"
                            showGrid={true}
                        />
                    </div>

                    <div className="space-y-4">
                        <div className="flex items-center space-x-4">
                            <ZoomOut className="h-4 w-4 text-gray-600" />
                            <input
                                type="range"
                                value={zoom}
                                min={1}
                                max={3}
                                step={0.1}
                                onChange={(e) => setZoom(parseFloat(e.target.value))}
                                className="w-full"
                            />
                            <ZoomIn className="h-4 w-4 text-gray-600" />
                        </div>

                        <div className="flex items-center space-x-4">
                            <span className="text-sm text-gray-600">Rotate</span>
                            <Button type="button" onClick={() => setRotation(rotation - 90)} className="rounded-md p-2">
                                <RotateCw className="h-4 w-4 rotate-90 transform" />
                            </Button>
                            <span className="text-sm">{rotation}°</span>
                            <Button type="button" onClick={() => setRotation(rotation + 90)} className="rounded-md p-2">
                                <RotateCw className="h-4 w-4 -rotate-90 transform" />
                            </Button>
                        </div>
                    </div>

                    <div className="flex justify-center space-x-4">
                        <Button
                            type="button"
                            onClick={handleCropComplete}
                            disabled={!croppedAreaPixels}
                            className="flex items-center rounded-md px-4 py-2 text-white disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Crop className="mr-2 h-4 w-4" />
                            Apply Crop
                        </Button>
                        <Button type="button" onClick={resetCropper} className="flex items-center rounded-md px-4 py-2 text-white">
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                    </div>
                </div>
            ) : (
                <div className="mt-2 rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                    <Upload className="mx-auto mb-4 h-12 w-12 text-gray-400" />
                    <p className="mb-4 text-gray-600">Select a square logo image</p>
                    <p className="mb-4 text-sm text-gray-500">Recommended: 800×800px</p>
                    <input ref={fileInputRef} type="file" accept="image/*" onChange={onFileChange} className="hidden" id="logo-upload" />
                    <label
                        htmlFor="logo-upload"
                        className="inline-flex cursor-pointer items-center rounded-md bg-[var(--btn-base-color)] px-4 py-2 text-white hover:bg-[var(--btn-base-hover-color)]"
                    >
                        <Upload className="mr-2 h-4 w-4" />
                        Select Logo
                    </label>
                </div>
            )}
        </div>
    );
};

export default LogoCropper;
