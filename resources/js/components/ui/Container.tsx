import React from 'react';

/**
 * Предустановленные размеры контейнера, соответствующие стандартным
 * брейкпоинтам Tailwind и наиболее распространённым разрешениям:
 * sm   640px   (мобильные ландшафт)
 * md   768px   (планшеты)
 * lg   1024px  (ноутбуки)
 * xl   1280px  (десктоп)
 * 2xl  1536px  (широкие экраны)
 * full без ограничений
 */
type ContainerSize = 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';

interface ContainerProps {
    children: React.ReactNode;
    /** Предустановленный размер, по умолчанию 'lg' (1024px) */
    size?: ContainerSize;
    /** Дополнительные CSS-классы */
    className?: string;
    /** Кастомная максимальная ширина (переопределяет size) */
    maxWidth?: number | string;
    /** Внутренние отступы по бокам, по умолчанию '1rem' */
    padding?: number | string;
}

const sizeToClass: Record<ContainerSize, string> = {
    sm: 'max-w-screen-sm',
    md: 'max-w-screen-md',
    lg: 'max-w-screen-lg',
    xl: 'max-w-screen-xl',
    '2xl': 'max-w-screen-2xl',
    full: 'max-w-full',
};

const Container: React.FC<ContainerProps> = (
    {
                                                 children,
                                                 size = 'lg',
                                                 className = '',
                                                 maxWidth,
                                                 padding = '1rem',
                                             }) => {
    // Если передан maxWidth – используем его, иначе Tailwind-класс
    const widthClass = maxWidth ? '' : sizeToClass[size];
    const style = maxWidth
        ? { maxWidth: typeof maxWidth === 'number' ? `${maxWidth}px` : maxWidth }
        : undefined;

    return (
        <div
            className={`mx-auto ${widthClass} ${className}`}
            style={{
                paddingLeft: padding,
                paddingRight: padding,
                ...style,
            }}
        >
            {children}
        </div>
    );
};

export default Container;
