import { EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline';
import { useState, type InputHTMLAttributes, type ReactNode } from 'react';

interface FormFieldProps extends InputHTMLAttributes<HTMLInputElement> {
    label: string;
    error?: string;
    /** Доп. элемент в строке лейбла справа (например, ссылка «Забыли пароль?»). */
    labelAction?: ReactNode;
}

/**
 * Для type="password" добавляем «глазок» справа, переключающий видимость
 * введённого пароля (input type="password" <-> "text").
 */
export function FormField({ label, error, id, type, labelAction, ...inputProps }: FormFieldProps) {
    const fieldId = id ?? inputProps.name;
    const [isPasswordVisible, setIsPasswordVisible] = useState(false);
    const isPassword = type === 'password';

    return (
        <div className="field flex flex-col gap-1.5">
            <div className="field-label-row">
                <label htmlFor={fieldId}>{label}</label>
                {labelAction}
            </div>
            {isPassword ? (
                <div className="field-password-wrap">
                    <input id={fieldId} type={isPasswordVisible ? 'text' : 'password'} {...inputProps} />
                    <button
                        type="button"
                        className="field-password-toggle"
                        onClick={() => setIsPasswordVisible((prev) => !prev)}
                        tabIndex={-1}
                        aria-label={isPasswordVisible ? 'Скрыть пароль' : 'Показать пароль'}
                    >
                        {isPasswordVisible ? <EyeSlashIcon /> : <EyeIcon />}
                    </button>
                </div>
            ) : (
                <input id={fieldId} type={type} {...inputProps} />
            )}
            {error ? <span className="field-error">{error}</span> : null}
        </div>
    );
}
