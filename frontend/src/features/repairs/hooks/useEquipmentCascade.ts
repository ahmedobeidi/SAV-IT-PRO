import { useEffect, useMemo, useRef, useState } from "react";
import { equipmentApi } from "../../equipment/equipment.api";
import type {
  EquipmentTypeRead,
  EquipmentBrandRead,
  EquipmentModelRead,
  Paginated,
} from "../../equipment/equipment.types";

export function useEquipmentCascade(initial?: {
  typeId?: number;
  brandId?: number;
  modelId?: number;
}) {
  const limit = 200;

  const initialTypeIdRef = useRef<number | "">(initial?.typeId ?? "");
  const initialBrandIdRef = useRef<number | "">(initial?.brandId ?? "");
  const initialModelIdRef = useRef<number | "">(initial?.modelId ?? "");

  const [types, setTypes] = useState<EquipmentTypeRead[]>([]);
  const [brands, setBrands] = useState<EquipmentBrandRead[]>([]);
  const [models, setModels] = useState<EquipmentModelRead[]>([]);

  const [typeId, setTypeId] = useState<number | "">(initialTypeIdRef.current);
  const [brandId, setBrandId] = useState<number | "">(initialBrandIdRef.current);
  const [modelId, setModelId] = useState<number | "">(initialModelIdRef.current);

  const [loadingTypes, setLoadingTypes] = useState(true);
  const [loadingBrands, setLoadingBrands] = useState(false);
  const [loadingModels, setLoadingModels] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const initializedBrands = useRef(false);
  const initializedModels = useRef(false);
  const prevTypeId = useRef<number | "">(initialTypeIdRef.current);
  const prevBrandId = useRef<number | "">(initialBrandIdRef.current);

  useEffect(() => {
    let alive = true;
    setLoadingTypes(true);
    setError(null);

    equipmentApi
      .listTypesSilent({ page: 1, limit })
      .then((res: Paginated<EquipmentTypeRead>) => {
        if (alive) setTypes(res.items);
      })
      .catch(() => {
        if (alive) setError("Impossible de charger les types d’équipement.");
      })
      .finally(() => {
        if (alive) setLoadingTypes(false);
      });

    return () => {
      alive = false;
    };
  }, []);

  const typeKey = useMemo(() => `${typeId}`, [typeId]);

  useEffect(() => {
    if (!typeId) {
      setBrands([]);
      setBrandId("");
      setModels([]);
      setModelId("");
      initializedBrands.current = false;
      initializedModels.current = false;
      prevTypeId.current = "";
      prevBrandId.current = "";
      return;
    }

    const typeChangedByUser =
      prevTypeId.current !== "" && prevTypeId.current !== typeId;

    prevTypeId.current = typeId;

    let alive = true;
    setLoadingBrands(true);
    setError(null);

    setBrands([]);
    setModels([]);
    setModelId("");

    if (typeChangedByUser) {
      setBrandId("");
      initializedBrands.current = true;
      initializedModels.current = true;
    }

    equipmentApi
      .listBrandsSilent(Number(typeId), { page: 1, limit })
      .then((res: Paginated<EquipmentBrandRead>) => {
        if (!alive) return;

        setBrands(res.items);

        if (!initializedBrands.current && initialBrandIdRef.current) {
          setBrandId(initialBrandIdRef.current as number);
        }

        initializedBrands.current = true;
      })
      .catch(() => {
        if (alive) setError("Impossible de charger les marques.");
      })
      .finally(() => {
        if (alive) setLoadingBrands(false);
      });

    return () => {
      alive = false;
    };
  }, [typeKey]);

  const brandKey = useMemo(() => `${brandId}`, [brandId]);

  useEffect(() => {
    if (!brandId) {
      setModels([]);
      setModelId("");
      prevBrandId.current = "";
      return;
    }

    const brandChangedByUser =
      prevBrandId.current !== "" && prevBrandId.current !== brandId;

    prevBrandId.current = brandId;

    let alive = true;
    setLoadingModels(true);
    setError(null);

    setModels([]);

    if (brandChangedByUser) {
      setModelId("");
      initializedModels.current = true;
    }

    equipmentApi
      .listModelsSilent(Number(brandId), { page: 1, limit })
      .then((res: Paginated<EquipmentModelRead>) => {
        if (!alive) return;

        setModels(res.items);

        if (!initializedModels.current && initialModelIdRef.current) {
          setModelId(initialModelIdRef.current as number);
        }

        initializedModels.current = true;
      })
      .catch(() => {
        if (alive) setError("Impossible de charger les modèles.");
      })
      .finally(() => {
        if (alive) setLoadingModels(false);
      });

    return () => {
      alive = false;
    };
  }, [brandKey]);

  return {
    typeId,
    brandId,
    modelId,
    setTypeId,
    setBrandId,
    setModelId,
    types,
    brands,
    models,
    loadingTypes,
    loadingBrands,
    loadingModels,
    error,
  };
}