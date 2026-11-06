import React, { useState } from "react";
import { Eye, EyeOff, Lock } from "lucide-react";

export default function PasswordInput({
    label = "Password",
    placeholder = "Enter your password",
    value,
    onChange,
    name = "password",
    className = "",
    error = "",
}) {
    const [showPassword, setShowPassword] = useState(false);

    const togglePasswordVisibility = () => {
        setShowPassword((prev) => !prev);
    };

    return (
        <div className={`w-full max-w-sm flex flex-col gap-1.5 ${className}`}>
            {label && (
                <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {label}
                </label>
            )}

            <div className="relative flex items-center">
                {/* Left Icon (Lock) */}
                <div className="absolute left-3 text-gray-400 pointer-events-none">
                    <Lock className="w-5 h-5" />
                </div>

                {/* Password Input Field */}
                <input
                    type={showPassword ? "text" : "password"}
                    name={name}
                    value={value}
                    onChange={onChange}
                    placeholder={placeholder}
                    className={`w-full pl-10 pr-11 py-2.5 bg-white dark:bg-gray-900 border text-gray-900 dark:text-white text-sm rounded-lg transition-all outline-none
            ${
                error
                    ? "border-red-500 focus:ring-2 focus:ring-red-500/20"
                    : "border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            }`}
                />

                {/* Right Toggle Button (Eye / EyeOff) */}
                <button
                    type="button"
                    onClick={togglePasswordVisibility}
                    aria-label={
                        showPassword ? "Hide password" : "Show password"
                    }
                    className="absolute right-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors focus:outline-none p-1 rounded-md"
                >
                    {showPassword ? (
                        <EyeOff className="w-5 h-5" />
                    ) : (
                        <Eye className="w-5 h-5" />
                    )}
                </button>
            </div>

            {/* Error Message if any */}
            {error && (
                <span className="text-xs text-red-500 mt-0.5">{error}</span>
            )}
        </div>
    );
}
