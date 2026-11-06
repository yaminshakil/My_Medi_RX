// Only for linting purposes, won't affect runtime

const _Image = typeof Image !== "undefined" ? Image : class {};
const _document =
    typeof document !== "undefined"
        ? document
        : {
              createElement: () => ({
                  getContext: () => ({}),
                  toBlob: () => {},
              }),
          };

export const createImage = (url) =>
    new Promise((resolve, reject) => {
        const image = new _Image();
        image.addEventListener("load", () => resolve(image));
        image.addEventListener("error", (error) => reject(error));
        image.setAttribute("crossOrigin", "anonymous");
        image.src = url;
    });

export const getCroppedImg = async (imageSrc, pixelCrop) => {
    if (!pixelCrop || !pixelCrop.width || !pixelCrop.height) {
        throw new Error("Invalid crop dimensions");
    }

    const image = await createImage(imageSrc);
    const canvas = _document.createElement("canvas");
    const ctx = canvas.getContext("2d");

    canvas.width = pixelCrop.width;
    canvas.height = pixelCrop.height;

    ctx.drawImage(
        image,
        pixelCrop.x,
        pixelCrop.y,
        pixelCrop.width,
        pixelCrop.height,
        0,
        0,
        pixelCrop.width,
        pixelCrop.height,
    );

    return new Promise((resolve) => {
        canvas.toBlob((blob) => resolve(blob ?? null), "image/png", 0.9);
    });
};
