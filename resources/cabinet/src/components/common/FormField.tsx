import type { InputHTMLAttributes } from 'react';

interface FormFieldProps extends InputHTMLAttributes<HTMLInputElement> {
    label: string;
    error?: string;
}

export function FormField({ label, error, id, ...inputProps }: FormFieldProps) {
    const fieldId = id ?? inputProps.name;

    return (
        <div className="field flex flex-col gap-1.5">
            <label htmlFor={fieldId}>{label}</label>
            <input id={fieldId} {...inputProps} />
            {error ? <span className="field-error">{error}</span> : null}
        </div>
    );
}
