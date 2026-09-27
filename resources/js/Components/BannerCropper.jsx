import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { getCroppedImg } from '@/lib/imageCropper';
import { Crop, Edit, RotateCw, Upload, X, ZoomIn, ZoomOut } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import Cropper from 'react-easy-crop';

const BannerCropper = ({ data, setData, field, fieldCropData, initialImage = null, initialCropData = null }) => {
    const [image, setImage] = useState(null);
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [rotation, setRotation] = useState(0);
    const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);
    const [croppedImage, setCroppedImage] = useState(null);
    const [isCropping, setIsCropping] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const fileInputRef = useRef(null);
    const aspectRatio = 16 / 9;

    // Initialize with existing image if provided (for edit mode)
    useEffect(() => {
        if (initialImage) {
            setCroppedImage(initialImage);

            // If we have existing crop data, pre-populate the form
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
            setIsEditing(true);
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
            const file = new File([croppedImageBlob], 'banner.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });

            // Create preview URL
            const previewUrl = URL.createObjectURL(croppedImageBlob);
            setCroppedImage(previewUrl);

            // Update form data
            setData((prev) => ({
                ...prev,
                [field]: file, // File object
                [fieldCropData]: JSON.stringify({
                    ...croppedAreaPixels,
                    rotation: rotation,
                }),
            }));

            // Reset cropping state
            setIsCropping(false);
            setImage(null);
        } catch (error) {
            console.error('Error cropping image:', error);
        }
    }, [image, croppedAreaPixels, rotation, setData]);

    const resetCropper = () => {
        setImage(null);
        setCroppedImage(null);
        setCrop({ x: 0, y: 0 });
        setZoom(1);
        setRotation(0);
        setCroppedAreaPixels(null);
        setIsCropping(false);

        // If we had an initial image, revert to it
        if (initialImage) {
            setCroppedImage(initialImage);
            setIsEditing(false);

            // Reset form data to initial values
            setData(field, null);
            setData(fieldCropData, initialCropData || '');
        } else {
            // If no initial image, clear everything
            setCroppedImage(null);
            setData(field, null);
            setData(fieldCropData, '');
        }

        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const removeBanner = () => {
        setCroppedImage(null);
        setData(field, null);
        setData(fieldCropData, '');
        setIsEditing(true);
    };

    const startEditing = () => {
        setIsEditing(true);
        // If we have an existing image, we need to load it for editing
        if (croppedImage && croppedImage.startsWith('blob:')) {
            // Already a blob URL, we can use it directly
            setImage(croppedImage);
        } else if (croppedImage) {
            // This is a server URL, we need to fetch it
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
                <div className="mt-2">
                    <img src={croppedImage} alt="Banner preview" className="h-48 max-w-full rounded border object-contain" />
                    <div className="mt-2 flex space-x-2">
                        <Button type="button" onClick={startEditing} className="flex items-center rounded-md px-3 py-1 text-sm text-white">
                            <Edit className="mr-1 h-4 w-4" />
                            Edit
                        </Button>
                        <Button variant="destructive" type="button" onClick={removeBanner} className="flex items-center text-sm">
                            <X className="mr-1 h-4 w-4" />
                            Remove
                        </Button>
                    </div>
                </div>
            ) : isCropping ? (
                <div className="space-y-4">
                    <div className="relative h-64 w-full overflow-hidden rounded-lg bg-gray-100">
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
                            <Input
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
                            <Button onClick={() => setRotation(rotation - 90)} className="p-2">
                                <RotateCw className="h-4 w-4 rotate-90 transform" />
                            </Button>
                            <span className="text-sm">{rotation}°</span>
                            <Button onClick={() => setRotation(rotation + 90)} className="p-2">
                                <RotateCw className="h-4 w-4 -rotate-90 transform" />
                            </Button>
                        </div>
                    </div>

                    <div className="flex space-x-4">
                        <Button
                            type="button"
                            onClick={handleCropComplete}
                            disabled={!croppedAreaPixels}
                            className="flex items-center text-white disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Crop className="mr-2 h-4 w-4" />
                            Apply Crop
                        </Button>
                        <Button
                            type="button"
                            onClick={resetCropper}
                            className="flex items-center rounded-md bg-red-700 px-4 py-2 text-white hover:bg-red-800"
                        >
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                    </div>
                </div>
            ) : (
                <div className="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                    <Upload className="mx-auto mb-4 h-12 w-12 text-gray-400" />
                    <p className="mb-4 text-gray-600">Select a image to crop</p>
                    <Input ref={fileInputRef} type="file" accept="image/*" onChange={onFileChange} className="hidden" id="banner-upload" />
                    <Label
                        htmlFor="banner-upload"
                        className="inline-flex cursor-pointer items-center rounded-lg bg-[var(--btn-base-color)] px-4 py-2 text-white hover:bg-[var(--btn-base-hover-color)]"
                    >
                        <Upload className="mr-2 h-4 w-4" />
                        Select Image
                    </Label>
                </div>
            )}
        </div>
    );
};

export default BannerCropper;
