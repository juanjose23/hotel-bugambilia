import { useState, useMemo } from 'react';
import type { MenuItemData } from '../interfaces/restauranteInterfaces';

export const useFiltroMenuRestaurante = (menu: MenuItemData[] = []) => {
    const [selectedCategory, setSelectedCategory] = useState<string>('TODOS');
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [selectedTag, setSelectedTag] = useState<string | null>(null);

    const categories = useMemo(() => {
        if (!menu || menu.length === 0) {
            return [];
        }

        const cats = Array.from(new Set(menu.map((m) => m.categoria)));

        return ['TODOS', ...cats];
    }, [menu]);

    const allTags = useMemo(() => {
        if (!menu || menu.length === 0) {
            return [];
        }

        const tagsSet = new Set<string>();
        menu.forEach((item) => {
            item.etiquetas?.forEach((t) => tagsSet.add(t));
        });

        return Array.from(tagsSet);
    }, [menu]);

    const filteredMenu = useMemo(() => {
        return menu.filter((item) => {
            const matchCat =
                selectedCategory === 'TODOS' ||
                item.categoria.toLowerCase() === selectedCategory.toLowerCase();
            const matchSearch =
                !searchQuery ||
                item.nombre.toLowerCase().includes(searchQuery.toLowerCase()) ||
                item.descripcion
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase());
            const matchTag =
                !selectedTag ||
                (item.etiquetas && item.etiquetas.includes(selectedTag));

            return matchCat && matchSearch && matchTag;
        });
    }, [menu, selectedCategory, searchQuery, selectedTag]);

    return {
        selectedCategory,
        setSelectedCategory,
        searchQuery,
        setSearchQuery,
        selectedTag,
        setSelectedTag,
        categories,
        allTags,
        filteredMenu,
    };
};
