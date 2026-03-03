import { useEffect, useMemo, useState } from "react";
import { equipmentApi } from "../../equipment/equipment.api";
import type {
  EquipmentTypeRead,
  EquipmentBrandRead,
  EquipmentModelRead,
  Paginated,
} from "../../equipment/equipment.types";

export function useEquipmentCascade() {
  const limit = 200; // dropdown: load “enough”
  const [types, setTypes] = useState<EquipmentTypeRead[]>([]);
  const [brands, setBrands] = useState<EquipmentBrandRead[]>([]);
  const [models, setModels] = useState<EquipmentModelRead[]>([]);

  const [typeId, setTypeId] = useState<number | "">("");
  const [brandId, setBrandId] = useState<number | "">("");
  const [modelId, setModelId] = useState<number | "">("");

  const [loadingTypes, setLoadingTypes] = useState(true);
  const [loadingBrands, setLoadingBrands] = useState(false);
  const [loadingModels, setLoadingModels] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // load types on mount
  useEffect(() => {
    let alive = true;
    setLoadingTypes(true);
    setError(null);

    equipmentApi
      .listTypes({ page: 1, limit })
      .then((res: Paginated<EquipmentTypeRead>) => alive && setTypes(res.items))
      .catch(() => alive && setError("Impossible de charger les types d’équipement."))
      .finally(() => alive && setLoadingTypes(false));

    return () => {
      alive = false;
    };
  }, []);

  // when type changes → reset brand/model and load brands
  const typeKey = useMemo(() => `${typeId}`, [typeId]);
  useEffect(() => {
    if (!typeId) {
      setBrands([]);
      setBrandId("");
      setModels([]);
      setModelId("");
      return;
    }

    let alive = true;
    setLoadingBrands(true);
    setError(null);

    setBrands([]);
    setBrandId("");
    setModels([]);
    setModelId("");

    equipmentApi
      .listBrands(Number(typeId), { page: 1, limit })
      .then((res: Paginated<EquipmentBrandRead>) => alive && setBrands(res.items))
      .catch(() => alive && setError("Impossible de charger les marques."))
      .finally(() => alive && setLoadingBrands(false));

    return () => {
      alive = false;
    };
  }, [typeKey]);

  // when brand changes → reset model and load models
  const brandKey = useMemo(() => `${brandId}`, [brandId]);
  useEffect(() => {
    if (!brandId) {
      setModels([]);
      setModelId("");
      return;
    }

    let alive = true;
    setLoadingModels(true);
    setError(null);

    setModels([]);
    setModelId("");

    equipmentApi
      .listModels(Number(brandId), { page: 1, limit })
      .then((res: Paginated<EquipmentModelRead>) => alive && setModels(res.items))
      .catch(() => alive && setError("Impossible de charger les modèles."))
      .finally(() => alive && setLoadingModels(false));

    return () => {
      alive = false;
    };
  }, [brandKey]);

  return {
    // selections
    typeId,
    brandId,
    modelId,
    setTypeId,
    setBrandId,
    setModelId,

    // options
    types,
    brands,
    models,

    // state
    loadingTypes,
    loadingBrands,
    loadingModels,
    error,
  };
}